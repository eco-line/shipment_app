<?php

namespace App\Http\Controllers\v1\Shipment;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\ShipmentHistory;
use Illuminate\Support\Facades\Input;
use App\Helpers\Helper;

class PredictionController extends Controller
{
	const BOTHPINCODES_WT = 0.5; //where both pincodes match
	const ONEPINCODES_WT = 0.25; //where only one of the two pincodes match

	const OUTFORDELIVERY = 305;

    public function predict_ofd(){ //predict_out_for_delivery
    	$pickup_pincode = Input::get('pickup_pincode');
    	$drop_pincode = Input::get('drop_pincode');
    	$awb = Input::get('awb');

    	if(empty($pickup_pincode) || empty($drop_pincode) || empty($awb)){
    		return array('status' => 204, 'message' => 'Please provide all required inputs');
    	}

    	//find current status of this shipment
    	$current_status = $this->fetch_current_shipment_status($awb);

		$probability = $this->calculate_probability($pickup_pincode,$drop_pincode,$awb,$current_status);    	
    	return array('status' => 200, 'message' => "Probability of next day out for delivery is $probability");
    }

    public function fetch_current_shipment_status($awb){
    	//call api
		$url = config('external.shipment_api.url').'?awb='.$awb;
		$api_data = (new Helper())->curl_get($url);
		if(!empty($api_data)){
			$scan_detail_arr = json_decode($api_data)[0]->scan_detail;
			$len = count($scan_detail_arr);
			return $scan_detail_arr[$len-1];
		}
    }

    public function calculate_probability($pickup_pincode,$drop_pincode,$awb,$current_status){
    	$current_status_code = $current_status->status_code;
    	// if($current_status_code >= self::OUTFORDELIVERY){
    	// 	return array('status' => 200, 'message' => 'Shipment is already in out of delivery or further state.');
    	// }
    	
    	// $both_pincodes_probab = $this->find_pincodes_probability($pickup_pincode,$drop_pincode,$current_status_code,self::BOTHPINCODES_WT);
    	$pickup_pincode_probab = $this->find_pincodes_probability($pickup_pincode,0,$current_status_code,self::ONEPINCODES_WT);
    	print_r($pickup_pincode_probab);die;
		$drop_pincode_probab = $this->find_pincodes_probability(0,$drop_pincode,$current_status_code,self::ONEPINCODES_WT);

		return $both_pincodes_probab + $pickup_pincode_probab + $drop_pincode_probab;
    }

    public function find_pincodes_probability($pickup_pincode,$drop_pincode,$current_status_code,$weight){    	
    	$time_diff_both = $this->pincodes_data($pickup_pincode,$drop_pincode,$current_status_code);

    	$within_day = 0;
    	$total_time = 0;
    	foreach ($time_diff_both as $single_time_diff) {
    		if($single_time_diff <= 24){
    			$within_day = $within_day + $single_time_diff;
    		}
    		$total_time = $total_time + $single_time_diff;
    	}
    	if($total_time != 0){
	    	$probab = $within_day/$total_time;
	    	
	    	$weighted_probab = $weight*$probab;
	    	return $weighted_probab;    	
    	}else{
    		return 0;
    	}
    }

    public function pincodes_data($pickup_pincode,$drop_pincode,$current_status_code){
    	$similar_data = (new ShipmentHistory())->find_similar_data($pickup_pincode,$drop_pincode,$current_status_code,self::OUTFORDELIVERY);
    	$awbs = array();
    	$time_diff = array();

    	foreach ($similar_data as $ship_history) {
    		if(!in_array($ship_history['awb'], $awbs)){

    			if(!isset($start_time) && $ship_history['status_code'] == $current_status_code){
    				$start_time = $ship_history['status_updated_at'];
    			}elseif(!isset($end_time) && $ship_history['status_code'] == self::OUTFORDELIVERY){
    				$end_time = $ship_history['status_updated_at'];
    			}
    			if(isset($start_time) && isset($end_time)){
    				$time_diff[] = date_diff(date_create($end_time),date_create($start_time))->h; //gives differnce in hours
    				array_push($awbs, $ship_history['awb']);
    				unset($start_time);
    				unset($end_time);
    			}

    		}
    	}
    	return $time_diff;   	
    }
}

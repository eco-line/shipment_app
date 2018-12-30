<?php

namespace App\Console\Commands\Courier;

use Illuminate\Console\Command;
use App\Helpers\Helper;
use App\Repositories\Shipments;
use App\Repositories\ShipmentHistory;


class ShipmentDailyFetch extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'fetch_shipment:schedule';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Fetch Awb status every hour';

	const BULKLIMIT = 500;

	public function __construct()
	{
		parent::__construct();
	}

	public function handle()
	{
		echo "Reading data from csv\n";
		//read csv
		$filePath = public_path('files/tracking_ids.csv');

		$fileHandle = fopen($filePath, "r");
		if ($fileHandle === FALSE){
		    die('Error opening '.$filePath);
		}

		$offset = 0;
		$bulk = 0;
		while(!feof($fileHandle)){
		    fseek($fileHandle, $offset); //Go to where we were when we ended the last batch

		    $i = 0;
	        $db_shipment_insert = array();
	        $db_shipment_history_insert = array();
	        $csv_array = array();
	        $awbs_string = NULL;

		    while (($currRow = fgetcsv($fileHandle)) !== FALSE){
		        $i++;
		        echo "Fetching Row - ".$i."\n";

		        if(!empty($currRow[0]) && !empty($currRow[1]) && !empty($currRow[2])){
		        	array_push($csv_array, $currRow);
		        	if($awbs_string == NULL){
		        		$awbs_string = $currRow[0];
		        	}else{
		        		$awbs_string = $awbs_string.','.$currRow[0]; 	
		        	}
		        }

		        if($i >= self::BULKLIMIT){
		            $offset = ftell($fileHandle); //Update current position in the file
		            //bulk fetch api data
			        $data_arr = $this->fetch_shipment_data($awbs_string,$csv_array,$bulk);

			        if(!empty($data_arr)){
			        	foreach ($data_arr as $single_node) {

				        	if($single_node['shipment'] && $single_node['shipment_history']){
				        		array_push($db_shipment_insert, $single_node['shipment']);
				        		foreach ($single_node['shipment_history'] as $sh_arr) {
				        			array_push($db_shipment_history_insert, $sh_arr);
				        		}
				        	}

			        	}
			        }

		            //bulk update in db
		            $this->bulk_update_data($db_shipment_insert,$db_shipment_history_insert);
		            //reinitializxe array
			        $db_shipment_insert = array();
			        $db_shipment_history_insert = array();
			        $csv_array = array();
			        $awbs_string = NULL;
			        $bulk++;
		            break;
		        }
		    }
		}
		fclose($fileHandle);
	}

	public function fetch_shipment_data($awbs_string,$csv_row,$bulk)
	{		
		//call api
		$url = config('external.shipment_api.url').'?awb='.$awbs_string;
		$api_data = (new Helper())->curl_get($url);
		//update data in db
		if(!empty($api_data)){
			try{
				echo "Fetching shipment data for bulk no - $bulk\n";
				\Log::info('Fetching shipment data', ['bulk' => $bulk]);

				return $this->update_shipment_data($csv_row,$api_data);
			}catch(\Exception $Exception){
				echo "Error in Fetching shipment data for bulk no - $bulk\n";
				\Log::error('Fetching shipment data into db failed', ['bulk' => $bulk,'Exception'  => $Exception->getTraceAsString()]);
			}
		}
		return false;
	}

	public function update_shipment_data($csv_row,$data)
	{
		$data = json_decode($data);
		$result_arr = array();

		foreach ($data as $key => $value) {
			$shipment_array = array();

			if($value && $value->scan_detail && !empty($csv_row[$key][1]) && !empty($csv_row[$key][2])){
				$shipment_array = $value->scan_detail;
				$len = count($shipment_array);

				$ship_arr = array(
					'awb' => $shipment_array[$len-1]->awbno ? $shipment_array[$len-1]->awbno : NULL,
					'pickup_pincode' => $csv_row[$key][1],
					'drop_pincode' => $csv_row[$key][2],
					'order_no' => $shipment_array[$len-1]->orderno ? $shipment_array[$len-1]->orderno : NULL,
					'current_status_code' => $shipment_array[$len-1]->status_code ? $shipment_array[$len-1]->status_code : NULL,
					'current_status' => $shipment_array[$len-1]->status ? $shipment_array[$len-1]->status : NULL,
					'current_status_description' => $shipment_array[$len-1]->status_description ? $shipment_array[$len-1]->status_description : NULL,
					'remarks' => $shipment_array[$len-1]->remarks ? $shipment_array[$len-1]->remarks : NULL,
					'current_location' => $shipment_array[$len-1]->location ? $shipment_array[$len-1]->location : NULL,
					'status_updated_at' => $shipment_array[$len-1]->updated_date ? $shipment_array[$len-1]->updated_date : NULL
				);
				$ship_his_array = array();
				//insert all entries in shipment history table
				foreach ($shipment_array as $history_arr) {
					$shipment_history_arr = array(
						'awb' => $history_arr->awbno ? $history_arr->awbno : NULL,
						'status_code' => $history_arr->status_code ? $history_arr->status_code : NULL,
						'status' => $history_arr->status ? $history_arr->status : NULL,
						'status_description' => $history_arr->status_description ? $history_arr->status_description : NULL,
						'remarks' => $history_arr->remarks ? $history_arr->remarks : NULL,
						'location' => $history_arr->location ? $history_arr->location : NULL,
						'status_updated_at' => $history_arr->updated_date ? $history_arr->updated_date : NULL,
					);
					array_push($ship_his_array, $shipment_history_arr);
				}
				array_push($result_arr, array('shipment' => $ship_arr, 'shipment_history' => $ship_his_array));
			}
		}
		return $result_arr;
	}

	public function bulk_update_data($shipment,$shipment_history){
		Shipments::insert($shipment);
		ShipmentHistory::insert($shipment_history);
		echo "Bulk inserted data\n";
		return true;
	}

}
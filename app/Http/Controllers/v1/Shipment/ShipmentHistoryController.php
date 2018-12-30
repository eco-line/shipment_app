<?php

namespace App\Http\Controllers\v1\Shipment;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\ShipmentHistory;

class ShipmentHistoryController extends Controller
{
    public function get_tracking($awb){
    	// check for this awb in shipment_history table;
    	$awb_history = ShipmentHistory::join('shipments','shipments.awb','=','shipments_history.awb')
    									->where('shipments_history.awb', $awb)
    									->select('shipments.awb','shipments.pickup_pincode','shipments.drop_pincode','shipments_history.status_code','shipments_history.status','shipments_history.status_description','shipments_history.remarks','shipments_history.location','shipments_history.status_updated_at')
    									->orderBy('shipments_history.status_updated_at','desc')
						 				->get();
		if(!empty($awb_history)){
			return array('status' => 200, 'data' => $awb_history);
		}else{
			return array('status' => 204, 'data' => 'No data available');
		}
    }
}

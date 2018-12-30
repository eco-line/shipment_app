<?php

namespace App\Http\Controllers\v1\Shipment;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ShipmentHistoryController extends Controller
{
    public function get_tracking($awb){
    	print_r($awb);die;
    }
}

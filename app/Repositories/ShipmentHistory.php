<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;

class ShipmentHistory extends Model
{
	protected $table = 'shipments_history';

	protected $primaryKey = 'id';
	
	public $incrementing = true;
	
	protected $fillable = ['*'];
	
	protected static $unguarded = true;

    public function find_similar_data($pickup_pincode=0,$drop_pincode=0,$status_code,$out_for_delivery,$limit,$offset){
    	//305 status code is out for delivery
    	$data = self::join('shipments','shipments.awb','=','shipments_history.awb')
    									->where(function ($query) use ($status_code,$out_for_delivery){
											return $query->where('shipments_history.status_code',$status_code)
														 ->orWhere('shipments_history.status_code',$out_for_delivery);
										})
    									->select('shipments.awb','shipments.pickup_pincode','shipments.drop_pincode','shipments_history.status_code','shipments_history.status','shipments_history.status_description','shipments_history.remarks','shipments_history.location','shipments_history.status_updated_at')
    									->orderBy('shipments_history.status_updated_at','desc');

		if($pickup_pincode != 0){
			$data = $data->where('shipments.pickup_pincode', $pickup_pincode);
		}
		if($drop_pincode != 0){
			$data = $data->where('shipments.drop_pincode', $drop_pincode);
		}

		$data = $data->take($limit)->skip($offset)->get();

		return $data->toArray();   	
    }
}

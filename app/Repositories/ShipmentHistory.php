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
}

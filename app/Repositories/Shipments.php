<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;

class Shipments extends Model
{
	protected $table = 'shipments';
	protected $primaryKey = 'id';
	public $incrementing = true;
	protected $fillable = ['*'];
}

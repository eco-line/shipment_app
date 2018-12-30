<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/tracking/{awb}', 'v1\Shipment\ShipmentHistoryController@get_tracking');

Route::get('/probability', 'v1\Shipment\PredictionController@predict_ofd'); //predict_out_for_delivery


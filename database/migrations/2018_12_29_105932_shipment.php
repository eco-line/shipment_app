<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Shipment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shipment', function (Blueprint $table) {
            $table->increments('id');
            
            $table->string('awb');
            $table->index('awb');
            
            $table->integer('pickup_pincode');
            $table->integer('drop_pincode');
            $table->string('order_no');

            $table->integer('current_status_code');
            $table->string('current_status');
            $table->string('current_status_description');
            $table->string('remarks');
            $table->string('current_location');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('shipment');
    }
}

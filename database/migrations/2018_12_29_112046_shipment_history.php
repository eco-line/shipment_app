<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ShipmentHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shipments_history', function (Blueprint $table) {
            $table->increments('id');
            
            $table->integer('shipment_id')->unsigned();
            $table->index('shipment_id');
            $table->foreign('shipment_id')->references('id')->on('shipments')->onDelete('cascade');

            $table->integer('status_code');
            $table->string('status');
            $table->string('status_description');
            $table->string('remarks');
            $table->string('location');

            $table->string('status_updated_at');
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
        Schema::drop('shipments_history');
    }
}

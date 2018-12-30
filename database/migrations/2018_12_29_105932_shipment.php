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
        Schema::create('shipments', function (Blueprint $table) {
            $table->increments('id');
            
            $table->string('awb')->nullable();
            $table->index('awb');

            $table->integer('pickup_pincode')->nullable();
            $table->integer('drop_pincode')->nullable();
            $table->string('order_no')->nullable();

            $table->string('current_status_code')->nullable();
            $table->string('current_status')->nullable();
            $table->string('current_status_description')->nullable();
            $table->string('remarks')->nullable();
            $table->string('current_location')->nullable();

            $table->timestamp('status_updated_at')->nullable();
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
        Schema::drop('shipments');
    }
}

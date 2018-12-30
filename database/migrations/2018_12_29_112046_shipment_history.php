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

            $table->string('awb')->nullable();
            $table->index('awb');

            $table->string('status_code')->nullable();
            $table->string('status')->nullable();
            $table->string('status_description')->nullable();
            $table->string('remarks')->nullable();
            $table->string('location')->nullable();

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
        Schema::drop('shipments_history');
    }
}

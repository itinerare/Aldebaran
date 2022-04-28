<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemovePriceDataFromCommissions extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('commissions', function (Blueprint $table) {
            //
            $table->dropColumn('paid_status');
            $table->dropColumn('cost_data');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('commissions', function (Blueprint $table) {
            //
            $table->bool('paid_status')->default(0);
            $table->longText('cost_data')->nullable()->default(null);
        });
    }
}

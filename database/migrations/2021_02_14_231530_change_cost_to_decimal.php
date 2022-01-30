<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeCostToDecimal extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('commissions', function (Blueprint $table) {
            $table->decimal('cost', 13, 2)->default(0.00)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('commissions', function (Blueprint $table) {
            $table->integer('cost')->nullable(false)->default(0)->change();
        });
    }
}

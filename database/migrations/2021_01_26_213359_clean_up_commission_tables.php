<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CleanUpCommissionTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('commissioners', function (Blueprint $table) {
            //
            $table->dropColumn('name');
            $table->dropColumn('times_commed');
        });

        Schema::table('commissions', function (Blueprint $table) {
            //
            $table->string('commission_key')->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('commissioners', function (Blueprint $table) {
            //
            $table->string('name');
            $table->integer('times_commed')->unsigned();
        });

        Schema::table('commissions', function (Blueprint $table) {
            //
            $table->string('commission_key')->change();
        });
    }
}

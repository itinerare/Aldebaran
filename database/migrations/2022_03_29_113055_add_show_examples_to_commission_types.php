<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShowExamplesToCommissionTypes extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('commission_types', function (Blueprint $table) {
            //
            $table->boolean('show_examples')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('commission_types', function (Blueprint $table) {
            //
            $table->dropColumn('show_examples');
        });
    }
}

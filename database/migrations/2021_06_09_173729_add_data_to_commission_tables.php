<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDataToCommissionTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('commission_categories', function (Blueprint $table) {
            // Add data column
            $table->text('data')->nullable()->default(null);
        });

        Schema::table('commission_types', function (Blueprint $table) {
            // Change data column to text
            $table->text('data')->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('commission_tables', function (Blueprint $table) {
            //
            $table->dropColumn('data');
        });

        Schema::table('commission_types', function (Blueprint $table) {
            // Change data column to text
            $table->string('data', 1024)->nullable()->default(null)->change();
        });
    }
}

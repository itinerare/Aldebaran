<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCommissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('commissions', function (Blueprint $table) {
            //
            $table->text('data')->nullable()->default(null)->change();
            $table->dropColumn('description');
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
        Schema::table('commissions', function (Blueprint $table) {
            //
            $table->text('data')->nullable()->default(null)->change();
            $table->text('description')->nullable()->default(null);
        });
    }
}

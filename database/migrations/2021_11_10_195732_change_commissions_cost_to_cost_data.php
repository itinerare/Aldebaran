<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeCommissionsCostToCostData extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::table('commissions', function (Blueprint $table) {
            //
            $table->text('cost')->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::table('commissions', function (Blueprint $table) {
            //
            $table->renameColumn('cost_data', 'cost');
        });
    }
}

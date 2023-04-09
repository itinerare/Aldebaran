<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::table('commission_quotes', function (Blueprint $table) {
            //
            $table->integer('commission_id')->unsigned()->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::table('commission_quotes', function (Blueprint $table) {
            //
            $table->dropColumn('commission_id');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::table('commissioners', function (Blueprint $table) {
            //
            $table->boolean('receive_notifications')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::table('commissioners', function (Blueprint $table) {
            //
            $table->dropColumn('receive_notifications');
        });
    }
};

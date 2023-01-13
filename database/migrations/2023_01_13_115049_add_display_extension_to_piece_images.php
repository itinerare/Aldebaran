<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::table('piece_images', function (Blueprint $table) {
            //
            $table->string('display_extension')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::table('piece_images', function (Blueprint $table) {
            //
            $table->dropColumn('display_extension');
        });
    }
};

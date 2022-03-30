<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsPrimaryImageToPieceImages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('piece_images', function (Blueprint $table) {
            //
            $table->boolean('is_primary_image')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('piece_images', function (Blueprint $table) {
            //
            $table->dropColumn('is_primary_image');
        });
    }
}
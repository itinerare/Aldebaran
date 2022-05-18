<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('piece_literatures', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('piece_id')->unsigned()->index();

            $table->text('text')->nullable()->default(null);

            // Used for if a custom thumbnail is provided.
            $table->string('hash')->nullable()->default(null);
            $table->string('extension')->nullable()->default(null);

            $table->boolean('is_primary')->default(1);
            $table->boolean('is_visible')->default(1);
            $table->integer('sort')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('piece_literatures');
    }
};

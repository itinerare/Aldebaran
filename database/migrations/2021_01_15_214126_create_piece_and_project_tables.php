<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePieceAndProjectTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');

            $table->string('name');
            $table->text('description')->nullable()->default(null);
            $table->boolean('is_visible')->default(0);
        });

        Schema::create('pieces', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('project_id')->unsigned()->index();

            $table->string('name');
            $table->text('description')->nullable()->default(null);
            // Time at which the piece was made. Separate from create/upload time in the event they are disparate for whatever reason.
            $table->timestamp('timestamp')->nullable()->default(null);
            $table->boolean('is_visible')->default(1);

            $table->timestamps();
        });

        Schema::create('piece_images', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('piece_id')->unsigned()->index();

            $table->string('hash');
            $table->string('fullsize_hash');
            $table->string('extension');

            $table->string('description')->nullable()->default(null);
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');

            $table->string('name');
            $table->text('description')->nullable()->default(null);

            $table->boolean('is_active')->default(1);
        });

        Schema::create('piece_tags', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');

            $table->integer('piece_id')->unsigned()->index();
            $table->integer('tag_id')->unsigned()->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('projects');
        Schema::dropIfExists('pieces');
        Schema::dropIfExists('piece_images');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('piece_tags');
    }
}

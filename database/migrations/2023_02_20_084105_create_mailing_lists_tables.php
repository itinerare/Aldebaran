<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::create('mailing_lists', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name');
            $table->text('description')->nullable();

            $table->boolean('is_open');
        });

        Schema::create('mailing_list_entries', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('mailing_list_id')->unsigned()->index();

            $table->string('subject');
            $table->text('text');
            $table->boolean('is_draft');

            $table->timestamps();
        });

        Schema::create('mailing_list_subscribers', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('mailing_list_id')->unsigned()->index();

            $table->string('email');
            $table->integer('last_entry_sent')->nullable()->default(null);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::dropIfExists('mailing_lists');
        Schema::dropIfExists('mailing_list_entries');
        Schema::dropIfExists('mailing_list_subscribers');
    }
};

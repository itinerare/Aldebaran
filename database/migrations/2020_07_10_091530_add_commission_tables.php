<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCommissionTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add tables for comm types, subtypes, commissions, and commissioners.
        Schema::create('comm_types', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');

            $table->string('type');
            $table->string('name');
            $table->string('data', 1024);
            $table->timestamps();
        });

        Schema::create('commissions', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');

            $table->string('key');
            $table->integer('commissioner_id')->unsigned();
            $table->integer('comm_type')->unsigned();
            $table->string('status');
            $table->string('data', 1024);
            $table->timestamps();
        });

        Schema::create('commissioners', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');

            $table->string('name');
            $table->string('email')->unique();
            $table->integer('times_commed')->unsigned();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('comm_types');
        Schema::dropIfExists('commissions');
        Schema::dropIfExists('commissioners');
    }
}

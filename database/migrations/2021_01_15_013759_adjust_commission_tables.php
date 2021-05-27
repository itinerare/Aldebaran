<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AdjustCommissionTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('commission_categories', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');

            $table->string('name');
            $table->boolean('is_active')->default(0);
        });

        // Drop old table and create new, better-structured one...
        Schema::dropIfExists('comm_types');
        Schema::create('commission_types', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('category_id')->unsigned()->index();

            $table->string('name');
            // Includes open/close status, slots open
            $table->string('availability', 1024);
            $table->text('description')->nullable()->default(null);
            // Includes min-max price, info about extras
            $table->string('data', 1024);
            $table->boolean('is_active')->default(0);

            $table->timestamps();
        });

        Schema::table('commissions', function (Blueprint $table) {
            // Adjust existing columns
            $table->dropColumn('comm_type');
            $table->index('commissioner_id');

            // Add new columns
            // Comm type ID
            $table->integer('commission_type')->unsigned()->index();
            // 0 = Unpaid, 1 = Partially paid, 2 = Paid in full
            $table->integer('paid_status')->default(0);
            $table->text('description')->nullable()->default(null);
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
        Schema::dropIfExists('commission_categories');

        Schema::table('commissions', function (Blueprint $table) {
            //
            $table->integer('comm_type')->unsigned();
            $table->dropIndex(['commissioner_id']);

            $table->dropColumn('commission_type');
            $table->dropColumn('paid_status');
            $table->dropColumn('description');
        });

        Schema::dropIfExists('commission_types');
        Schema::create('comm_types', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');

            $table->string('type');
            $table->string('name');
            $table->string('data', 1024);
            $table->timestamps();
        });
    }
}

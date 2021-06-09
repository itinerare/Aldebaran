<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCommissionClassTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create commission class table
        Schema::create('commission_classes', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');

            $table->string('name');
            $table->string('slug');
            $table->boolean('is_active')->default(1);
            $table->integer('sort')->default(0);

            $table->text('data')->nullable()->default(null);
        });

        // Update commission categories table to suit
        Schema::table('commission_categories', function (Blueprint $table) {
            // Create a new column for class ID, and set it to default to 1 as a contingency/
            // for backwards-compatability
            $table->integer('class_id')->default(1)->index();

            $table->dropIndex(['type']);
            $table->dropColumn('type');
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
        Schema::dropIfExists('commission_classes');

        Schema::table('commission_categories', function (Blueprint $table) {
            $table->dropIndex(['class_id']);
            $table->dropColumn('class_id');

            $table->string('type')->default('art')->index();
        });
    }
}

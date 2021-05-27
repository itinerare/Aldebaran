<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInfoToCommissioners extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('commissioners', function (Blueprint $table) {
            //
            $table->string('contact')->nullable()->default(null);
            $table->string('paypal');
            $table->boolean('is_banned')->default(0);
            $table->softDeletes();
        });

        Schema::create('commissioner_ips', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('commissioner_id')->index();
            $table->string('ip')->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('commissioners', function (Blueprint $table) {
            //
            $table->dropColumn('contact');
            $table->dropColumn('paypal');
            $table->dropColumn('is_banned');
            $table->dropColumn('deleted_at');
        });

        Schema::dropIfExists('commissioner_ips');
    }
}

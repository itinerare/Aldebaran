<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::table('commission_payments', function (Blueprint $table) {
            //
            $table->string('invoice_id')->nullable()->default(null);
        });

        Schema::table('commissioners', function (Blueprint $table) {
            //
            $table->string('customer_id')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::table('commission_payments', function (Blueprint $table) {
            //
            $table->dropColumn('invoice_id');
        });

        Schema::table('commission_payments', function (Blueprint $table) {
            //
            $table->dropColumn('customer_id');
        });
    }
};

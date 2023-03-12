<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::table('commissions', function (Blueprint $table) {
            // This should not change per commission,
            // so it is easiest to set and store it at the commission level
            $table->string('payment_processor')->default('paypal');
        });

        Schema::table('commissioners', function (Blueprint $table) {
            // Make payment email clearer and more generic
            // This also suits standard elsewhere e.g. in tests
            $table->renameColumn('paypal', 'payment_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::table('commissions', function (Blueprint $table) {
            //
            $table->dropColumn('payment_processor');
        });

        Schema::table('commissioners', function (Blueprint $table) {
            //
            $table->renameColumn('payment_email', 'paypal');
        });
    }
};

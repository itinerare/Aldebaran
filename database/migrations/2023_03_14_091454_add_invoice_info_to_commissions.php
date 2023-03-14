<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::table('commission_classes', function (Blueprint $table) {
            //
            $table->text('invoice_data')->nullable()->default(null);
        });

        Schema::table('commission_categories', function (Blueprint $table) {
            //
            $table->text('invoice_data')->nullable()->default(null);
        });

        Schema::table('commission_types', function (Blueprint $table) {
            //
            $table->text('invoice_data')->nullable()->default(null);
        });

        Schema::table('commissions', function (Blueprint $table) {
            //
            $table->text('invoice_data')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::table('commission_classes', function (Blueprint $table) {
            //
            $table->dropColumn('invoice_data');
        });

        Schema::table('commission_categories', function (Blueprint $table) {
            //
            $table->dropColumn('invoice_data');
        });

        Schema::table('commission_types', function (Blueprint $table) {
            //
            $table->dropColumn('invoice_data');
        });

        Schema::table('commissions', function (Blueprint $table) {
            //
            $table->dropColumn('invoice_data');
        });
    }
};

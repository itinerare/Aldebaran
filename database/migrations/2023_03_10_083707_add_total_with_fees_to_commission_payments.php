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
            $table->decimal('total_with_fees', 13, 2)->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::table('commission_payments', function (Blueprint $table) {
            //
            $table->dropColumn('total_with_fees');
        });
    }
};

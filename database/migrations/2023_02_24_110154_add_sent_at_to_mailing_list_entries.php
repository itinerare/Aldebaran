<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::table('mailing_list_entries', function (Blueprint $table) {
            $table->timestamp('sent_at')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::table('mailing_list_entries', function (Blueprint $table) {
            $table->dropColumn('sent_at');
        });
    }
};

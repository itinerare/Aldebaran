<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommissionPaymentsTable extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::create('commission_payments', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('commission_id')->unsigned()->index();

            $table->decimal('cost', 13, 2)->default(0.00);
            $table->decimal('tip', 13, 2)->default(0.00);

            $table->boolean('is_paid')->default(0);
            $table->boolean('is_intl')->default(0);

            $table->timestamp('paid_at')->nullable()->default(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::dropIfExists('commission_payments');
    }
}

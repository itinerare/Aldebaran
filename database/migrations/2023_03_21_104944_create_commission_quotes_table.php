<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::create('commission_quotes', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('quote_key')->nullable()->default(null);

            $table->integer('commissioner_id')->unsigned()->index();
            $table->integer('commission_type_id')->unsigned()->index();
            $table->string('status');

            $table->string('subject', 255)->nullable()->default(null);
            $table->text('description');
            $table->text('comments')->nullable()->default(null);
            $table->decimal('amount', 13, 2)->default(0.00);

            $table->timestamps();
        });

        Schema::table('commission_types', function (Blueprint $table) {
            //
            $table->boolean('quotes_open')->default(0);
            $table->boolean('quote_required')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::dropIfExists('commission_quotes');

        Schema::table('commission_types', function (Blueprint $table) {
            //
            $table->dropColumn('quotes_open');
            $table->dropColumn('quote_required');
        });
    }
};

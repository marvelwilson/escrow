<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('ref_id');
            $table->string('user_status');
            $table->string('paired_id');
            $table->string('amount');
            $table->string('installment');
            $table->json('percent');
            $table->string('invoice');
            $table->string('service');
            $table->text('desc');
            $table->string('date');
            $table->string('acc_number');
            $table->string('bname');
            $table->string('bhname');
            $table->string('status');
            $table->string('payment_status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
};

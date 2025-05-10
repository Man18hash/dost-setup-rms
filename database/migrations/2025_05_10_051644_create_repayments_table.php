<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRepaymentsTable extends Migration
{
    public function up()
    {
        Schema::create('repayments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expected_schedule_id')
                  ->constrained('expected_schedules')
                  ->cascadeOnDelete();
            $table->decimal('payment_amount', 15, 2)->default(0);
            $table->date('payment_date')->nullable();
            $table->string('or_number')->nullable();
            $table->date('or_date')->nullable();
            $table->decimal('penalty_amount', 15, 2)->default(0);
            $table->boolean('returned_check')->default(false);
            $table->date('pdc_date')->nullable();
            $table->string('remarks', 255)->nullable();
            $table->timestamps();

            // indexes for faster lookups
            $table->index('payment_date');
            $table->index('returned_check');
        });
    }

    public function down()
    {
        Schema::dropIfExists('repayments');
    }
}

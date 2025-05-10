<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSetupsTable extends Migration
{
    public function up()
    {
        Schema::create('setups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('beneficiary_id')->constrained()->cascadeOnDelete();
            $table->foreignId('province_id')->constrained()->cascadeOnDelete();
            $table->string('project_title');
            $table->string('check_number')->nullable();
            $table->decimal('amount_assisted', 15, 2);
            $table->date('check_date');
            $table->date('refund_start');
            $table->date('refund_end');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('setups');
    }
}
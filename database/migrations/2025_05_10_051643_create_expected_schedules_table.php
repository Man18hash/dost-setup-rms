<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExpectedSchedulesTable extends Migration
{
    public function up()
    {
        Schema::create('expected_schedules', function (Blueprint $table) {
            // make sure this is a BIGINT unsigned primary key
            $table->bigIncrements('id');

            // must match setups.id which should also be BIGINT unsigned
            $table->unsignedBigInteger('setup_id');
            $table->foreign('setup_id')
                  ->references('id')
                  ->on('setups')
                  ->cascadeOnDelete();

            $table->date('due_date');
            $table->decimal('amount_due', 15, 2);
            $table->smallInteger('months_lapsed')->default(0);

            // one per setup‐month
            $table->unique(['setup_id', 'due_date']);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('expected_schedules');
    }
}

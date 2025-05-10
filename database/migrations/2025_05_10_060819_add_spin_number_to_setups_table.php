<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSpinNumberToSetupsTable extends Migration
{
    public function up()
    {
        Schema::table('setups', function (Blueprint $table) {
            // Inserts spin_number right after the id column, make it unique
            $table->string('spin_number')->after('id')->unique();
        });
    }

    public function down()
    {
        Schema::table('setups', function (Blueprint $table) {
            $table->dropColumn('spin_number');
        });
    }
}

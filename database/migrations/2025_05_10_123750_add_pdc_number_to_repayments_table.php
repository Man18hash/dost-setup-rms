<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPdcNumberToRepaymentsTable extends Migration
{
    public function up()
    {
        Schema::table('repayments', function (Blueprint $table) {
            $table->string('pdc_number')->nullable()->after('pdc_date');
        });
    }

    public function down()
    {
        Schema::table('repayments', function (Blueprint $table) {
            $table->dropColumn('pdc_number');
        });
    }
}

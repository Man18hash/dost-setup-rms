<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('repayments', function (Blueprint $table) {
            // 1) Add deferred flag right after returned_check
            $table->boolean('deferred')
                  ->default(false)
                  ->after('returned_check');

            // 2) Add the date you deferred
            $table->date('deferred_date')
                  ->nullable()
                  ->after('deferred');
        });
    }

    public function down(): void
    {
        Schema::table('repayments', function (Blueprint $table) {
            $table->dropColumn(['deferred_date', 'deferred']);
        });
    }
};

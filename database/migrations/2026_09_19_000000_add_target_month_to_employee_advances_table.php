<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('employee_advances', 'target_month')) {
            Schema::table('employee_advances', function (Blueprint $table) {
                $table->string('target_month')->nullable()->after('advance_date');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('employee_advances', 'target_month')) {
            Schema::table('employee_advances', function (Blueprint $table) {
                $table->dropColumn('target_month');
            });
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('treatment_infos', function (Blueprint $table) {
            $table->decimal('global_price', 12, 2)->default(0)->after('description');
            $table->decimal('remaining_amount', 12, 2)->default(0)->after('global_price');
        });

        Schema::table('treatment_infos', function (Blueprint $table) {
            $table->dropColumn(['quantity', 'unit_price', 'line_total']);
        });

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE treatment_infos ADD CONSTRAINT treatment_infos_global_price_non_negative CHECK (global_price >= 0)');
            DB::statement('ALTER TABLE treatment_infos ADD CONSTRAINT treatment_infos_remaining_amount_non_negative CHECK (remaining_amount >= 0)');
        }
    }

    public function down(): void
    {
        Schema::table('treatment_infos', function (Blueprint $table) {
            $table->unsignedInteger('quantity')->default(1)->after('description');
            $table->decimal('unit_price', 12, 2)->default(0)->after('quantity');
            $table->decimal('line_total', 12, 2)->default(0)->after('unit_price');
            $table->dropColumn(['global_price', 'remaining_amount']);
        });
    }
};

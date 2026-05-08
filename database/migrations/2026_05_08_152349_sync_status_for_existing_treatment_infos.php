<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('treatment_infos')
            ->where('remaining_amount', '<=', 0)
            ->update(['status' => 'paid']);
            
        DB::table('treatment_infos')
            ->where('remaining_amount', '>', 0)
            ->update(['status' => 'unpaid']);
    }

    public function down(): void
    {
        // No need to reverse status syncing as it's data migration
    }
};

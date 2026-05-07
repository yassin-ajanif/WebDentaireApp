<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('treatment_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('treatment_info_id')->constrained('treatment_infos')->cascadeOnDelete();
            $table->timestamp('session_date');
            $table->decimal('received_payment', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['treatment_info_id', 'session_date']);
        });

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE treatment_sessions ADD CONSTRAINT treatment_sessions_received_payment_non_negative CHECK (received_payment >= 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('treatment_sessions');
    }
};

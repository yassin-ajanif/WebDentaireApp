<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('treatment_session_corrections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('treatment_session_id')->constrained('treatment_sessions')->cascadeOnDelete();
            $table->foreignId('treatment_info_id')->constrained('treatment_infos')->cascadeOnDelete();
            $table->timestamp('old_session_date');
            $table->timestamp('new_session_date');
            $table->decimal('old_received_payment', 12, 2);
            $table->decimal('new_received_payment', 12, 2);
            $table->text('old_notes')->nullable();
            $table->text('new_notes')->nullable();
            $table->text('reason');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['treatment_session_id', 'created_at']);
            $table->index(['treatment_info_id', 'created_at']);
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('treatment_session_corrections');
    }
};

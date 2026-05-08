<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('treatment_corrections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('treatment_info_id')->constrained('treatment_infos')->cascadeOnDelete();
            $table->decimal('old_global_price', 12, 2);
            $table->decimal('new_global_price', 12, 2);
            $table->text('old_description');
            $table->text('new_description');
            $table->text('reason');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['treatment_info_id', 'created_at']);
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('treatment_corrections');
    }
};

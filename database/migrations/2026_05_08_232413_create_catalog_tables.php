<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('treatment_catalog', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('activity_catalog', function (Blueprint $table) {
            $table->id();
            $table->foreignId('treatment_catalog_id')->constrained('treatment_catalog')->cascadeOnDelete();
            $table->string('activity_name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_catalog');
        Schema::dropIfExists('treatment_catalog');
    }
};

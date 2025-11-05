<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('import_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_batch_id')->constrained()->onDelete('cascade');
            $table->integer('row_index'); // Excel row number
            $table->json('row_data'); // Original row data from Excel
            $table->json('mapped_data')->nullable(); // Data after mapping
            $table->enum('status', ['pending', 'valid', 'duplicate', 'error', 'skipped', 'imported', 'updated'])->default('pending');
            $table->json('errors')->nullable(); // Validation errors for this row
            $table->string('duplicate_key')->nullable(); // Key that caused duplicate
            $table->foreignId('existing_record_id')->nullable(); // Reference to existing record if duplicate
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            
            $table->index(['import_batch_id', 'status']);
            $table->index('row_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_logs');
    }
};

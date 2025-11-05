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
        Schema::create('import_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('entity_type')->default('fixed_assets'); // Type of entity being imported
            $table->string('original_filename');
            $table->string('stored_filename');
            $table->integer('total_rows')->default(0);
            $table->integer('processed_rows')->default(0);
            $table->integer('success_rows')->default(0);
            $table->integer('failed_rows')->default(0);
            $table->integer('duplicate_rows')->default(0);
            $table->integer('updated_rows')->default(0);
            $table->enum('status', ['pending', 'mapping', 'validating', 'processing', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->json('mapping_config')->nullable(); // Column mapping configuration
            $table->json('import_summary')->nullable(); // Summary of import results
            $table->json('validation_errors')->nullable(); // Validation errors summary
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index('entity_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_batches');
    }
};

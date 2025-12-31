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
        Schema::table('fixed_assets', function (Blueprint $table) {
            // Make previously required fields nullable
            $table->string('tipe_fixed_asset')->nullable()->change();
            $table->string('nama_fixed_asset')->nullable()->change();
            $table->integer('taksiran_umur')->nullable()->change();
            $table->date('efektif_mulai')->nullable()->change();
            $table->string('lokasi')->nullable()->change();
            $table->string('pic')->nullable()->change();
            
            // Note: kode tetap unique tapi bisa nullable untuk auto-generation
            // Unique constraint sudah ada, jadi hanya ubah ke nullable
            $table->string('kode')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fixed_assets', function (Blueprint $table) {
            // Revert back to required (not nullable)
            $table->string('tipe_fixed_asset')->nullable(false)->change();
            $table->string('nama_fixed_asset')->nullable(false)->change();
            $table->integer('taksiran_umur')->nullable(false)->change();
            $table->date('efektif_mulai')->nullable(false)->change();
            $table->string('lokasi')->nullable(false)->change();
            $table->string('pic')->nullable(false)->change();
            $table->string('kode')->nullable(false)->change();
        });
    }
};

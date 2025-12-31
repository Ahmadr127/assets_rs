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
        Schema::create('print_formats', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nama format (e.g., "Stiker Besar 6x5 cm")
            $table->string('code')->unique(); // Kode unik (e.g., "6x5", "5x3")
            $table->decimal('width_cm', 5, 2); // Lebar dalam cm
            $table->decimal('height_cm', 5, 2); // Tinggi dalam cm
            $table->integer('qr_size_px')->default(120); // Ukuran QR code dalam pixel
            $table->integer('margin_mm')->default(5); // Margin dalam mm
            $table->integer('font_size_name')->default(11); // Font size untuk nama barang
            $table->integer('font_size_code')->default(10); // Font size untuk kode
            $table->boolean('is_active')->default(true); // Status aktif
            $table->boolean('is_default')->default(false); // Default format
            $table->integer('sort_order')->default(0); // Urutan tampilan
            $table->text('description')->nullable(); // Deskripsi
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('print_formats');
    }
};

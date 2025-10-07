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
        Schema::create('fixed_assets', function (Blueprint $table) {
            $table->id();
            $table->string('tipe_fixed_asset'); // Tipe Fixed Asset
            $table->string('kode')->unique(); // Kode
            $table->string('kode_manual')->nullable(); // Kode Manual
            $table->string('nama_fixed_asset'); // Nama Fixed Asset
            $table->integer('taksiran_umur'); // Taksiran Umur (Thn)
            $table->date('efektif_mulai'); // Efektif Mulai
            $table->text('deskripsi')->nullable(); // Deskripsi
            $table->string('lokasi'); // Lokasi
            $table->enum('status', ['aktif', 'tidak_aktif', 'maintenance', 'rusak'])->default('aktif'); // Status
            $table->enum('kondisi', ['baik', 'rusak_ringan', 'rusak_berat', 'tidak_layak'])->default('baik'); // Kondisi
            $table->string('vendor')->nullable(); // Vendor
            $table->string('brand')->nullable(); // Brand
            $table->string('code_type')->nullable(); // Code Type
            $table->string('serial_number')->nullable(); // Serial Number
            $table->string('pic'); // PIC (Person In Charge)
            $table->boolean('harus_dicek_fisik')->default(true); // Harus Dicek Fisik
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fixed_assets');
    }
};

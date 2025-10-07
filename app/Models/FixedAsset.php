<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FixedAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'tipe_fixed_asset',
        'kode',
        'kode_manual',
        'nama_fixed_asset',
        'taksiran_umur',
        'efektif_mulai',
        'deskripsi',
        'lokasi',
        'status',
        'kondisi',
        'vendor',
        'brand',
        'code_type',
        'serial_number',
        'pic',
        'harus_dicek_fisik'
    ];

    protected $casts = [
        'efektif_mulai' => 'date',
        'harus_dicek_fisik' => 'boolean',
        'taksiran_umur' => 'integer'
    ];

    // Validation rules
    public static function rules($id = null)
    {
        return [
            'tipe_fixed_asset' => 'required|string|max:255',
            'kode' => 'required|string|max:255|unique:fixed_assets,kode' . ($id ? ',' . $id : ''),
            'kode_manual' => 'nullable|string|max:255',
            'nama_fixed_asset' => 'required|string|max:255',
            'taksiran_umur' => 'required|integer|min:1|max:100',
            'efektif_mulai' => 'required|date',
            'deskripsi' => 'nullable|string',
            'lokasi' => 'required|string|max:255',
            'status' => 'required|in:aktif,tidak_aktif,maintenance,rusak',
            'kondisi' => 'required|in:baik,rusak_ringan,rusak_berat,tidak_layak',
            'vendor' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:255',
            'code_type' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'pic' => 'required|string|max:255',
            'harus_dicek_fisik' => 'boolean'
        ];
    }

    // Status options
    public static function getStatusOptions()
    {
        return [
            'aktif' => 'Aktif',
            'tidak_aktif' => 'Tidak Aktif',
            'maintenance' => 'Maintenance',
            'rusak' => 'Rusak'
        ];
    }

    // Condition options
    public static function getConditionOptions()
    {
        return [
            'baik' => 'Baik',
            'rusak_ringan' => 'Rusak Ringan',
            'rusak_berat' => 'Rusak Berat',
            'tidak_layak' => 'Tidak Layak'
        ];
    }

    // Accessor for status display
    public function getStatusDisplayAttribute()
    {
        return self::getStatusOptions()[$this->status] ?? $this->status;
    }

    // Accessor for condition display
    public function getConditionDisplayAttribute()
    {
        return self::getConditionOptions()[$this->kondisi] ?? $this->kondisi;
    }

    // Scope for active assets
    public function scopeActive($query)
    {
        return $query->where('status', 'aktif');
    }

    // Scope for search
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('nama_fixed_asset', 'like', "%{$search}%")
              ->orWhere('kode', 'like', "%{$search}%")
              ->orWhere('kode_manual', 'like', "%{$search}%")
              ->orWhere('lokasi', 'like', "%{$search}%")
              ->orWhere('pic', 'like', "%{$search}%")
              ->orWhere('vendor', 'like', "%{$search}%")
              ->orWhere('brand', 'like', "%{$search}%");
        });
    }
}

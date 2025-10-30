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
        'harus_dicek_fisik',
        // normalized FKs
        'location_id',
        'status_id',
        'condition_id',
        'vendor_id',
        'brand_id',
        'asset_type_id',
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
            'tipe_fixed_asset' => 'nullable|string|max:255',
            'kode' => 'required|string|max:255|unique:fixed_assets,kode' . ($id ? ',' . $id : ''),
            'kode_manual' => 'nullable|string|max:255',
            'nama_fixed_asset' => 'required|string|max:255',
            'taksiran_umur' => 'required|integer|min:1|max:100',
            'efektif_mulai' => 'required|date',
            'deskripsi' => 'nullable|string',
            // denormalized legacy fields (kept nullable during transition)
            'lokasi' => 'nullable|string|max:255',
            'status' => 'nullable|in:aktif,tidak_aktif,maintenance,rusak',
            'kondisi' => 'nullable|in:baik,rusak_ringan,rusak_berat,tidak_layak',
            'vendor' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:255',
            'code_type' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'pic' => 'required|string|max:255',
            'harus_dicek_fisik' => 'boolean',
            // normalized foreign keys
            'location_id' => 'required|exists:locations,id',
            'status_id' => 'required|exists:asset_statuses,id',
            'condition_id' => 'required|exists:asset_conditions,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'brand_id' => 'nullable|exists:brands,id',
            'asset_type_id' => 'required|exists:asset_types,id',
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

    // Accessor for age display
    public function getAgeDisplayAttribute()
    {
        if (!$this->efektif_mulai) {
            return '-';
        }

        $years = $this->efektif_mulai->diffInYears(now());
        
        if ($years >= 1) {
            return $years . ' thn';
        }
        
        $months = $this->efektif_mulai->diffInMonths(now());
        if ($months >= 1) {
            return $months . ' bln';
        }
        
        $days = $this->efektif_mulai->diffInDays(now());
        return $days . ' hari';
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
              ->orWhere('pic', 'like', "%{$search}%");
        })
        ->orWhereHas('location', function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%");
        })
        ->orWhereHas('statusRef', function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%");
        })
        ->orWhereHas('conditionRef', function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%");
        })
        ->orWhereHas('vendorRef', function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%");
        })
        ->orWhereHas('brandRef', function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%");
        })
        ->orWhereHas('typeRef', function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%");
        });
    }

    // Relationships (normalized)
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function statusRef()
    {
        return $this->belongsTo(AssetStatus::class, 'status_id');
    }

    public function conditionRef()
    {
        return $this->belongsTo(AssetCondition::class, 'condition_id');
    }

    public function vendorRef()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function brandRef()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function typeRef()
    {
        return $this->belongsTo(AssetType::class, 'asset_type_id');
    }
}

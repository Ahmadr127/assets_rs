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
        'po',
        'asset_number',
        'nama_fixed_asset',
        'taksiran_umur',
        'nilai_awal',
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
        'taksiran_umur' => 'integer',
        'nilai_awal' => 'decimal:2'
    ];

    // Validation rules
    public static function rules($id = null)
    {
        return [
            'tipe_fixed_asset' => 'nullable|string|max:255',
            'kode' => 'nullable|string|max:255', // kode can be duplicate (no unique constraint)
            'kode_manual' => 'nullable|string|max:255|unique:fixed_assets,kode_manual' . ($id ? ',' . $id : ''),
            'po' => 'nullable|string|max:255',
            'asset_number' => 'nullable|string|max:255',
            'nama_fixed_asset' => 'nullable|string|max:255',
            'taksiran_umur' => 'nullable|integer|min:1|max:100',
            'nilai_awal' => 'nullable|numeric|min:0',
            'efektif_mulai' => 'nullable|date',
            'deskripsi' => 'nullable|string',
            // denormalized legacy fields (nullable with defaults)
            'lokasi' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:255',
            'kondisi' => 'nullable|string|max:255',
            'vendor' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:255',
            'code_type' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'pic' => 'nullable|string|max:255',
            'harus_dicek_fisik' => 'nullable|boolean',
            // normalized foreign keys
            'location_id' => 'nullable|exists:locations,id',
            'status_id' => 'nullable|exists:asset_statuses,id',
            'condition_id' => 'nullable|exists:asset_conditions,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'brand_id' => 'nullable|exists:brands,id',
            'asset_type_id' => 'nullable|exists:asset_types,id',
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

        $years = (int) $this->efektif_mulai->diffInYears(now());
        
        if ($years >= 1) {
            return $years . ' thn';
        }
        
        $months = (int) $this->efektif_mulai->diffInMonths(now());
        if ($months >= 1) {
            return $months . ' bln';
        }
        
        $days = (int) $this->efektif_mulai->diffInDays(now());
        return $days . ' hari';
    }
    
    // Accessor for taksiran_umur to ensure it's always integer
    public function getTaksiranUmurAttribute($value)
    {
        if ($value === null || $value === '') {
            return null;
        }
        // Force to integer, even if stored as decimal in old data
        return (int) round((float) $value);
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

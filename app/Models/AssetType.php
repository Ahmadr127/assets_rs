<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'description'];

    public function fixedAssets()
    {
        return $this->hasMany(FixedAsset::class);
    }
}

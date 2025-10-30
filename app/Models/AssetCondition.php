<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetCondition extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description'];

    public function fixedAssets()
    {
        return $this->hasMany(FixedAsset::class, 'condition_id');
    }
}

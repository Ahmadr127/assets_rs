<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportLog extends Model
{
    protected $fillable = [
        'import_batch_id',
        'row_index',
        'row_data',
        'mapped_data',
        'status',
        'errors',
        'duplicate_key',
        'existing_record_id',
        'processed_at',
    ];

    protected $casts = [
        'row_data' => 'array',
        'mapped_data' => 'array',
        'errors' => 'array',
        'processed_at' => 'datetime',
    ];

    // Relationships
    public function batch(): BelongsTo
    {
        return $this->belongsTo(ImportBatch::class, 'import_batch_id');
    }

    public function existingRecord(): BelongsTo
    {
        return $this->belongsTo(FixedAsset::class, 'existing_record_id');
    }

    // Scopes
    public function scopeForBatch($query, $batchId)
    {
        return $query->where('import_batch_id', $batchId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeValid($query)
    {
        return $query->where('status', 'valid');
    }

    public function scopeErrors($query)
    {
        return $query->where('status', 'error');
    }

    public function scopeDuplicates($query)
    {
        return $query->where('status', 'duplicate');
    }

    // Helper methods
    public function hasErrors(): bool
    {
        return $this->status === 'error' && !empty($this->errors);
    }

    public function isDuplicate(): bool
    {
        return $this->status === 'duplicate';
    }

    public function isValid(): bool
    {
        return $this->status === 'valid';
    }

    public function getErrorMessages(): array
    {
        return $this->errors ?? [];
    }
}

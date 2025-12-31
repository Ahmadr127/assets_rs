<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImportBatch extends Model
{
    protected $fillable = [
        'user_id',
        'entity_type',
        'original_filename',
        'stored_filename',
        'total_rows',
        'processed_rows',
        'success_rows',
        'failed_rows',
        'duplicate_rows',
        'updated_rows',
        'status',
        'mapping_config',
        'import_summary',
        'validation_errors',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'mapping_config' => 'array',
        'import_summary' => 'array',
        'validation_errors' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'total_rows' => 'integer',
        'processed_rows' => 'integer',
        'success_rows' => 'integer',
        'failed_rows' => 'integer',
        'duplicate_rows' => 'integer',
        'updated_rows' => 'integer',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ImportLog::class);
    }

    // Scopes
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    // Helper methods
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isProcessing(): bool
    {
        return in_array($this->status, ['processing', 'validating']);
    }

    public function canProcess(): bool
    {
        return in_array($this->status, ['pending', 'mapping']);
    }

    public function getProgressPercentage(): float
    {
        if ($this->total_rows === 0) {
            return 0;
        }
        return round(($this->processed_rows / $this->total_rows) * 100, 2);
    }

    public function getSuccessRate(): float
    {
        if ($this->processed_rows === 0) {
            return 0;
        }
        return round(($this->success_rows / $this->processed_rows) * 100, 2);
    }
}

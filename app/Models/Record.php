<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Record extends Model
{
    protected $fillable = [
        'participant_id',
        'canary_assessment_id',
        'survey_type',
        'status',
        'duration',
        'audio_url',
        'audio_format',
        'upload_urls',
        'response_data',
        'scores',
        'error_message',
        'started_at',
        'uploaded_at',
        'processed_at'
    ];

    protected $casts = [
        'upload_urls' => 'array',
        'response_data' => 'array',
        'scores' => 'array',
        'started_at' => 'datetime',
        'uploaded_at' => 'datetime',
        'processed_at' => 'datetime'
    ];

    // Relaciones
    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }

    // Scopes útiles
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    // Helpers
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    public function hasFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function getStressScore(): ?float
    {
        return $this->scores['stress'] ?? null;
    }

    public function getMoodScore(): ?float
    {
        return $this->scores['mood'] ?? null;
    }

    public function getEnergyScore(): ?float
    {
        return $this->scores['energy'] ?? null;
    }
}
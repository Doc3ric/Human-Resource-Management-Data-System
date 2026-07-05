<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'sha256_hash',
        'original_filename',
        'mime_type',
        'size_bytes',
        'storage_path',
        'capture_source',
        'attachment_field',
        'extraction_tier',
        'ocr_text',
        'ocr_confidence',
        'ocr_status',
        'doc_type_code',
        'importance_class',
        'privacy_tier',
        'abstract_description',
        'is_spi',
        'is_raccs',
        'encrypted',
        'personnel_id',
        'personnel_type',
        'status',
        'review_reason',
        'ingested_by',
    ];

    protected $casts = [
        'is_spi' => 'boolean',
        'is_raccs' => 'boolean',
        'encrypted' => 'boolean',
        'ocr_confidence' => 'decimal:2',
    ];

    public function ingestedBy()
    {
        return $this->belongsTo(User::class, 'ingested_by');
    }

    public function relations()
    {
        return $this->hasMany(DocumentRelation::class);
    }

    public function spiAccessLogs()
    {
        return $this->hasMany(SpiAccessLog::class);
    }

    public function raccsAccessLogs()
    {
        return $this->hasMany(RaccsAccessLog::class);
    }
}

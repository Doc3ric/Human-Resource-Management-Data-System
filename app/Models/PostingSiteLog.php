<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostingSiteLog extends Model
{
    protected $table = 'posting_site_log';

    protected $fillable = [
        'publication_request_id',
        'site_label',
        'posted_date',
        'removed_date',
        'proof_photo_path',
        'posted_by_id',
    ];

    protected $casts = [
        'posted_date' => 'date',
        'removed_date' => 'date',
    ];

    public function publicationRequest()
    {
        return $this->belongsTo(PublicationRequest::class);
    }

    public function postedBy()
    {
        return $this->belongsTo(User::class, 'posted_by_id');
    }
}

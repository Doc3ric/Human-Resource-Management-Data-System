<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ESignature extends Model
{
    protected $table = 'e_signatures';

    protected $fillable = [
        'signable_type', 'signable_id', 'signatory_name', 'signatory_position',
        'signature_image', 'signed_by', 'signed_at', 'ip_address',
        'pnpki_status', 'pnpki_reference',
    ];

    protected $casts = ['signed_at' => 'datetime'];

    public function signable()
    {
        return $this->morphTo();
    }

    public function signer()
    {
        return $this->belongsTo(User::class, 'signed_by');
    }
}

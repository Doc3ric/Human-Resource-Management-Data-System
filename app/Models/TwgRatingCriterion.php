<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TwgRatingCriterion extends Model
{
    protected $fillable = [
        'criterion_key', 'criterion_name', 'criterion_category',
        'score_basis_description', 'point_value', 'rating_scale_key',
        'is_active', 'sort_order',
    ];

    protected $casts = [
        'point_value' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function scores()
    {
        return $this->hasMany(TwgScore::class, 'criterion_id');
    }
}

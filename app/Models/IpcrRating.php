<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IpcrRating extends Model
{
    protected $fillable = [
        'plantilla_record_id',
        'period_type',
        'custom_period',
        'year',
        'target_submitted',
        'target_submission_date',
        'rating',
        'rating_submission_date',
        'final_rating',
    ];

    protected $casts = [
        'target_submitted' => 'boolean',
        'rating' => 'decimal:2',
        'final_rating' => 'decimal:2',
        'target_submission_date' => 'date',
        'rating_submission_date' => 'date',
    ];

    protected $appends = ['adjectival_rating'];

    public function getAdjectivalRatingAttribute()
    {
        $rating = $this->final_rating ?? $this->rating;
        if (!$rating) return '-';

        $threshPoor = (float) \App\Models\Setting::getVal('thresh_poor', 2);
        $threshUnsat = (float) \App\Models\Setting::getVal('thresh_unsat', 3);
        $threshSat = (float) \App\Models\Setting::getVal('thresh_sat', 4);
        $threshVsat = (float) \App\Models\Setting::getVal('thresh_vsat', 5);

        if ($rating <= $threshPoor) {
            return 'Poor';
        } elseif ($rating < $threshUnsat) {
            return 'Unsatisfactory';
        } elseif ($rating < $threshSat) {
            return 'Satisfactory';
        } elseif ($rating < $threshVsat) {
            return 'Very Satisfactory';
        } else {
            return 'Outstanding';
        }
    }

    public function plantillaRecord()
    {
        return $this->belongsTo(PlantillaRecord::class, 'plantilla_record_id');
    }

    protected static function booted()
    {
        static::saving(function ($ipcr) {
            // Only calculate final rating if there is an initial rating
            if (is_null($ipcr->rating)) {
                $ipcr->final_rating = null;
                return;
            }

            $deduction = 0;
            $year = $ipcr->year;

            $jjTargetStr = \App\Models\Setting::getVal('jan_jun_target_deadline', '12-15');
            $jjRatingStr = \App\Models\Setting::getVal('jan_jun_rating_deadline', '07-30');
            $jdTargetStr = \App\Models\Setting::getVal('jul_dec_target_deadline', '06-15');
            $jdRatingStr = \App\Models\Setting::getVal('jul_dec_rating_deadline', '01-30');

            if ($ipcr->period_type === 'jan-jun') {
                $p = explode('-', $jjTargetStr);
                $targetDeadline = \Carbon\Carbon::createFromDate($year - 1, $p[0] ?? 12, $p[1] ?? 15);
                
                $p2 = explode('-', $jjRatingStr);
                $ratingDeadline = \Carbon\Carbon::createFromDate($year, $p2[0] ?? 7, $p2[1] ?? 30);
            } elseif ($ipcr->period_type === 'jul-dec') {
                $p = explode('-', $jdTargetStr);
                $targetDeadline = \Carbon\Carbon::createFromDate($year, $p[0] ?? 6, $p[1] ?? 15);
                
                $p2 = explode('-', $jdRatingStr);
                $ratingDeadline = \Carbon\Carbon::createFromDate($year + 1, $p2[0] ?? 1, $p2[1] ?? 30);
            }

            // Target deduction: from settings, default 0.25
            $targetPenalty = (float) \App\Models\Setting::getVal('target_penalty', 0.25);
            if ($targetDeadline) {
                if (!$ipcr->target_submitted) {
                    $deduction += $targetPenalty;
                } elseif ($ipcr->target_submission_date && $ipcr->target_submission_date->startOfDay()->gt($targetDeadline->startOfDay())) {
                    $deduction += $targetPenalty;
                }
            }

            // Rating deduction: from settings, default 0.50
            $ratingPenalty = (float) \App\Models\Setting::getVal('rating_penalty', 0.50);
            if ($ratingDeadline) {
                if ($ipcr->rating_submission_date && $ipcr->rating_submission_date->startOfDay()->gt($ratingDeadline->startOfDay())) {
                    $deduction += $ratingPenalty;
                }
            }

            $final = $ipcr->rating - $deduction;
            // Ensure final rating doesn't go below 0 (optional, but good practice)
            $ipcr->final_rating = max(0, $final);
        });
    }
}

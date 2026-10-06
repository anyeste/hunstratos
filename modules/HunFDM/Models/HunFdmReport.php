<?php

namespace Modules\HunFDM\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HunFdmReport extends Model
{
    protected $table = 'hun_fdm_reports';

    protected $fillable = [
        // Core
        'pirep_id', 'flight_id', 'aircraft_icao', 'flight_profile',
        // Scores
        'landing_score', 'fdm_deductions', 'final_score',
        'landing_score_component', 'procedure_score_component',
        // Summary
        'exceedance_count', 'critical_count', 'flight_duration_min',
        'exceedances', 'recorded_at',
        // Takeoff milestone
        'takeoff_weight', 'takeoff_flaps',
        'departure_heading_dev', 'departure_centerline_dev',
        'departure_lat', 'departure_lon',
        // Landing milestone
        'landing_weight', 'landing_rate', 'landing_g_force',
        'landing_pitch', 'landing_bank', 'landing_speed_ias',
        'landing_flaps', 'touchdown_lat', 'touchdown_lon',
        // Arrival milestone
        'arrival_threshold_dist', 'arrival_centerline_dev', 'arrival_heading_dev',
        // Fuel
        'landing_fuel',
        // Integrity flags
        'slew_used', 'sim_rate_exceeded', 'crashed', 'unlimited_fuel', 'total_pause_min',
        // Audit
        'scoring_snapshot',
    ];

    protected $casts = [
        'exceedances'      => 'array',
        'scoring_snapshot' => 'array',
        'slew_used'        => 'boolean',
        'sim_rate_exceeded'=> 'boolean',
        'crashed'          => 'boolean',
        'unlimited_fuel'   => 'boolean',
        'landing_score'    => 'integer',
        'fdm_deductions'   => 'integer',
        'final_score'      => 'integer',
        'landing_score_component'   => 'integer',
        'procedure_score_component' => 'integer',
    ];

    public function pirep(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Pirep::class, 'pirep_id');
    }

    /**
     * Returns whether this report should be flagged for admin review.
     */
    public function shouldFlag(): bool
    {
        $flagThreshold = (int) HunFdmSetting::get('flag_score_threshold', 40);
        $criticalCount = (int) HunFdmSetting::get('critical_flag_count', 1);

        return $this->final_score < $flagThreshold
            || $this->critical_count >= $criticalCount;
    }
}

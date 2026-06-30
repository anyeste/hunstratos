<?php

namespace Modules\HunFDM\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HunFdmReport extends Model
{
    protected $table = 'hun_fdm_reports';

    protected $fillable = [
        'pirep_id',
        'flight_id',
        'aircraft_icao',
        'flight_profile',
        'landing_score',
        'fdm_deductions',
        'final_score',
        'exceedance_count',
        'critical_count',
        'flight_duration_min',
        'exceedances',
        'recorded_at',
    ];

    protected $casts = [
        'exceedances'    => 'array',
        'landing_score'  => 'integer',
        'fdm_deductions' => 'integer',
        'final_score'    => 'integer',
    ];

    public function pirep(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Pirep::class, 'pirep_id');
    }
}

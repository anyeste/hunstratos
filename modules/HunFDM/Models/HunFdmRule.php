<?php

namespace Modules\HunFDM\Models;

use Illuminate\Database\Eloquent\Model;

class HunFdmRule extends Model
{
    protected $table = 'hun_fdm_rules';

    protected $fillable = [
        'key',
        'category',
        'label',
        'enabled',
        'deduction',
        'critical',
        'cooldown_sec',
        'once_per_flight',
        'notes',
    ];

    protected $casts = [
        'enabled'         => 'boolean',
        'critical'        => 'boolean',
        'once_per_flight' => 'boolean',
        'deduction'       => 'integer',
        'cooldown_sec'    => 'integer',
    ];
}

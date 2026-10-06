<?php

namespace Modules\HunFDM\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\HunFDM\Models\HunFdmSetting;

class HunFdmSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // ── Scoring weights ──────────────────────────────────────────────
            ['key' => 'landing_weight',            'value' => '0.40',  'type' => 'float',   'label' => 'Landing score weight (0.0–1.0)',                'group' => 'scoring'],
            ['key' => 'procedure_weight',          'value' => '0.60',  'type' => 'float',   'label' => 'Procedure score weight (0.0–1.0)',              'group' => 'scoring'],

            // ── Review thresholds ────────────────────────────────────────────
            ['key' => 'flag_score_threshold',      'value' => '40',    'type' => 'integer', 'label' => 'Auto-flag PIREPs below this score',            'group' => 'thresholds'],
            ['key' => 'critical_flag_count',       'value' => '1',     'type' => 'integer', 'label' => 'Min critical exceedances to force flag',       'group' => 'thresholds'],
            ['key' => 'auto_reject_score',         'value' => '0',     'type' => 'integer', 'label' => 'Auto-reject PIREPs below this score (0=off)',  'group' => 'thresholds'],
            ['key' => 'pause_time_threshold_min',  'value' => '10',    'type' => 'integer', 'label' => 'Pause time threshold (minutes) for I4 rule',   'group' => 'thresholds'],
            ['key' => 'low_fuel_threshold_kg',     'value' => '500',   'type' => 'integer', 'label' => 'Minimum fuel at landing (kg) for F2 rule',     'group' => 'thresholds'],

            // ── Repeat multiplier ────────────────────────────────────────────
            ['key' => 'repeat_multiplier_enabled', 'value' => 'true',  'type' => 'boolean', 'label' => 'Enable escalating repeat exceedance penalties', 'group' => 'repeat'],
            ['key' => 'repeat_mult_2nd',           'value' => '1.25',  'type' => 'float',   'label' => 'Repeat multiplier — 2nd occurrence',           'group' => 'repeat'],
            ['key' => 'repeat_mult_3rd',           'value' => '1.50',  'type' => 'float',   'label' => 'Repeat multiplier — 3rd occurrence',           'group' => 'repeat'],
            ['key' => 'repeat_mult_4th_plus',      'value' => '2.00',  'type' => 'float',   'label' => 'Repeat multiplier — 4th+ occurrence',          'group' => 'repeat'],

            // ── Phase multipliers ────────────────────────────────────────────
            ['key' => 'phase_multipliers', 'type' => 'json', 'label' => 'Per-phase deduction multipliers', 'group' => 'phases', 'value' => json_encode([
                'BOARDING'         => 0.0,
                'PREFLIGHT'        => 0.0,
                'PUSHBACK'         => 0.3,
                'TAXI'             => 0.5,
                'TAXIOUT'          => 0.5,
                'TAKEOFF'          => 1.5,
                'REJECTED_TAKEOFF' => 1.5,
                'CLIMB'            => 1.0,
                'CRUISE'           => 0.8,
                'DESCENT'          => 1.0,
                'APPROACH'         => 1.5,
                'FINAL'            => 2.0,
                'GO_AROUND'        => 1.5,
                'LANDED'           => 0.5,
                'TAXIIN'           => 0.5,
                'DEBOARDING'       => 0.0,
                'ARRIVED'          => 0.0,
            ])],
        ];

        foreach ($settings as $setting) {
            HunFdmSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        $this->command->info('[HunFDM] Seeded ' . count($settings) . ' FDM settings.');
    }
}

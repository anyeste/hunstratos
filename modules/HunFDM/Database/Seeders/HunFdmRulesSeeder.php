<?php

namespace Modules\HunFDM\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\HunFDM\Models\HunFdmRule;

class HunFdmRulesSeeder extends Seeder
{
    public function run(): void
    {
        $rules = [
            // ── Speed ────────────────────────────────────────────────────────
            ['key' => 'overspeedBelow10k',     'category' => 'Speed',     'label' => 'Overspeed below 10,000 ft (IAS > 250 kt)',              'deduction' => 15, 'critical' => true,  'cooldown_sec' => 10,  'once_per_flight' => false],
            ['key' => 'overspeedVmo',          'category' => 'Speed',     'label' => 'VMO overspeed',                                        'deduction' => 20, 'critical' => true,  'cooldown_sec' => 10,  'once_per_flight' => false],
            ['key' => 'overspeedMmo',          'category' => 'Speed',     'label' => 'MMO overspeed',                                        'deduction' => 20, 'critical' => true,  'cooldown_sec' => 10,  'once_per_flight' => false],
            ['key' => 'flapsOverspeed',        'category' => 'Speed',     'label' => 'Flap overspeed',                                       'deduction' => 15, 'critical' => true,  'cooldown_sec' => 10,  'once_per_flight' => false],
            ['key' => 'gearOverspeed',         'category' => 'Speed',     'label' => 'Gear overspeed',                                       'deduction' => 15, 'critical' => true,  'cooldown_sec' => 10,  'once_per_flight' => false],
            ['key' => 'taxiOverspeed',         'category' => 'Speed',     'label' => 'Taxi overspeed (GS > 30 kt)',                           'deduction' =>  5, 'critical' => false, 'cooldown_sec' => 15,  'once_per_flight' => false],

            // ── Vertical Speed ───────────────────────────────────────────────
            ['key' => 'excessiveVsDescent',    'category' => 'Vertical',  'label' => 'Excessive VS descent below 10,000 ft (< -2500 fpm)',    'deduction' => 10, 'critical' => false, 'cooldown_sec' => 10,  'once_per_flight' => false],
            ['key' => 'excessiveVsApproach',   'category' => 'Vertical',  'label' => 'Excessive VS on approach below 2,500 ft (< -1500 fpm)','deduction' => 15, 'critical' => false, 'cooldown_sec' => 10,  'once_per_flight' => false],
            ['key' => 'excessiveVsCruise',     'category' => 'Vertical',  'label' => 'Excessive VS in cruise above 10,000 ft (< -6000 fpm)', 'deduction' => 10, 'critical' => false, 'cooldown_sec' => 10,  'once_per_flight' => false],

            // ── Attitude / Structural ────────────────────────────────────────
            ['key' => 'excessiveBankAngle',    'category' => 'Attitude',  'label' => 'Excessive bank angle',                                  'deduction' => 10, 'critical' => false, 'cooldown_sec' => 10,  'once_per_flight' => false],
            ['key' => 'excessivePitchUp',      'category' => 'Attitude',  'label' => 'Excessive pitch up',                                    'deduction' => 10, 'critical' => false, 'cooldown_sec' => 10,  'once_per_flight' => false],
            ['key' => 'excessivePitchDown',    'category' => 'Attitude',  'label' => 'Excessive pitch down',                                  'deduction' => 10, 'critical' => false, 'cooldown_sec' => 10,  'once_per_flight' => false],
            ['key' => 'excessiveGForce',       'category' => 'Attitude',  'label' => 'Excessive G-force (airborne)',                          'deduction' => 15, 'critical' => true,  'cooldown_sec' => 10,  'once_per_flight' => false],
            ['key' => 'negativeGForce',        'category' => 'Attitude',  'label' => 'Negative G-force',                                      'deduction' => 15, 'critical' => true,  'cooldown_sec' => 10,  'once_per_flight' => false],
            ['key' => 'hardLanding',           'category' => 'Attitude',  'label' => 'Hard landing (G > 2.1 or VS < -800 fpm)',               'deduction' => 20, 'critical' => true,  'cooldown_sec' =>  0,  'once_per_flight' => true ],
            ['key' => 'veryHardLanding',       'category' => 'Attitude',  'label' => 'Very hard landing (G > 2.8 or VS < -1200 fpm)',         'deduction' => 35, 'critical' => true,  'cooldown_sec' =>  0,  'once_per_flight' => true ],

            // ── Procedures ───────────────────────────────────────────────────
            ['key' => 'lightsOffBelow10k',     'category' => 'Procedures','label' => 'Landing lights off below 10,000 ft',                    'deduction' =>  5, 'critical' => false, 'cooldown_sec' => 30,  'once_per_flight' => false],
            ['key' => 'gearExtendedCruise',    'category' => 'Procedures','label' => 'Gear extended during cruise above 10,000 ft',           'deduction' => 10, 'critical' => false, 'cooldown_sec' => 10,  'once_per_flight' => false],
            ['key' => 'wrongTakeoffFlaps',     'category' => 'Procedures','label' => 'No flaps set for takeoff',                              'deduction' => 10, 'critical' => true,  'cooldown_sec' =>  0,  'once_per_flight' => true ],
            ['key' => 'wrongLandingFlaps',     'category' => 'Procedures','label' => 'Suboptimal flap setting at landing',                    'deduction' =>  8, 'critical' => false, 'cooldown_sec' =>  0,  'once_per_flight' => true ],

            // ── Engine ───────────────────────────────────────────────────────
            ['key' => 'engineOutAirborne',     'category' => 'Engine',    'label' => 'Engine out while airborne',                             'deduction' => 25, 'critical' => true,  'cooldown_sec' => 30,  'once_per_flight' => false],
            ['key' => 'engineShutdownGround',  'category' => 'Engine',    'label' => 'All engines off during taxi (event log)',               'deduction' =>  0, 'critical' => false, 'cooldown_sec' => 60,  'once_per_flight' => false],
            ['key' => 'abnormalN1',            'category' => 'Engine',    'label' => 'Abnormal N1 (< 15%) on running engine in cruise',       'deduction' => 10, 'critical' => true,  'cooldown_sec' => 30,  'once_per_flight' => false],

            // ── Fuel ─────────────────────────────────────────────────────────
            ['key' => 'unlimitedFuel',         'category' => 'Fuel',      'label' => 'Unlimited fuel enabled (MSFS)',                         'deduction' =>100, 'critical' => true,  'cooldown_sec' =>  0,  'once_per_flight' => true ],
            ['key' => 'lowFuelLanding',        'category' => 'Fuel',      'label' => 'Fuel below minimum at landing',                        'deduction' => 10, 'critical' => true,  'cooldown_sec' =>  0,  'once_per_flight' => true ],

            // ── Weight ───────────────────────────────────────────────────────
            ['key' => 'mtowExceedance',        'category' => 'Weight',    'label' => 'Takeoff weight exceeds MTOW',                           'deduction' => 20, 'critical' => true,  'cooldown_sec' =>  0,  'once_per_flight' => true ],
            ['key' => 'mlwExceedance',         'category' => 'Weight',    'label' => 'Landing weight exceeds MLW',                            'deduction' => 20, 'critical' => true,  'cooldown_sec' =>  0,  'once_per_flight' => true ],

            // ── Simulator Integrity ──────────────────────────────────────────
            ['key' => 'slewUsed',             'category' => 'Integrity', 'label' => 'Slew mode used during flight',                          'deduction' =>100, 'critical' => true,  'cooldown_sec' =>  0,  'once_per_flight' => true ],
            ['key' => 'simRateAboveNormal',   'category' => 'Integrity', 'label' => 'Simulation rate above 1x during flight',                'deduction' => 50, 'critical' => true,  'cooldown_sec' =>  0,  'once_per_flight' => true ],
            ['key' => 'crashed',             'category' => 'Integrity', 'label' => 'Aircraft crashed',                                     'deduction' =>100, 'critical' => true,  'cooldown_sec' =>  0,  'once_per_flight' => true ],
            ['key' => 'pausedExcessively',    'category' => 'Integrity', 'label' => 'Total pause time exceeds threshold',                    'deduction' => 10, 'critical' => false, 'cooldown_sec' =>  0,  'once_per_flight' => true ],

            // ── Navigation & ATC ─────────────────────────────────────────────
            ['key' => 'transponderOff',       'category' => 'Navigation','label' => 'Transponder squawk 0000 while airborne',                'deduction' =>  5, 'critical' => false, 'cooldown_sec' => 30,  'once_per_flight' => false],
            ['key' => 'altimeterNotSet',      'category' => 'Navigation','label' => 'Altimeter setting deviates from QNH (> 0.10 inHg)',     'deduction' =>  5, 'critical' => false, 'cooldown_sec' => 60,  'once_per_flight' => false],

            // ── Runway Alignment (MSFS only) ─────────────────────────────────
            ['key' => 'departureHeadingDev',  'category' => 'Runway',    'label' => 'Departure heading deviation > 10° at liftoff',          'deduction' =>  5, 'critical' => false, 'cooldown_sec' =>  0,  'once_per_flight' => true ],
            ['key' => 'departureCenterlineDev','category' => 'Runway',   'label' => 'Departure centerline deviation > 15m at liftoff',       'deduction' =>  5, 'critical' => false, 'cooldown_sec' =>  0,  'once_per_flight' => true ],
            ['key' => 'arrivalHeadingDev',    'category' => 'Runway',    'label' => 'Arrival heading deviation > 10° at touchdown',          'deduction' =>  5, 'critical' => false, 'cooldown_sec' =>  0,  'once_per_flight' => true ],
            ['key' => 'arrivalCenterlineDev', 'category' => 'Runway',    'label' => 'Arrival centerline deviation > 15m at touchdown',       'deduction' =>  5, 'critical' => false, 'cooldown_sec' =>  0,  'once_per_flight' => true ],
            ['key' => 'longLanding',          'category' => 'Runway',    'label' => 'Long landing — threshold distance > 1000m',             'deduction' => 10, 'critical' => false, 'cooldown_sec' =>  0,  'once_per_flight' => true ],

            // ── Native Sim Warnings ──────────────────────────────────────────
            ['key' => 'stallWarning',         'category' => 'Warnings',  'label' => 'Stall warning triggered',                               'deduction' => 10, 'critical' => false, 'cooldown_sec' => 15,  'once_per_flight' => false],
            ['key' => 'overspeedWarningNative','category' => 'Warnings', 'label' => 'Native simulator overspeed warning',                    'deduction' =>  5, 'critical' => false, 'cooldown_sec' => 10,  'once_per_flight' => false],
        ];

        foreach ($rules as $rule) {
            HunFdmRule::updateOrCreate(
                ['key' => $rule['key']],
                array_merge(['enabled' => true, 'notes' => null], $rule)
            );
        }

        $this->command->info('[HunFDM] Seeded ' . count($rules) . ' FDM rules.');
    }
}

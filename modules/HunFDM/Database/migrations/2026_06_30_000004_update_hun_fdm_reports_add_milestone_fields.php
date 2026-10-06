<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hun_fdm_reports', function (Blueprint $table) {
            // Score components
            $table->unsignedTinyInteger('landing_score_component')->default(0)->after('final_score');
            $table->unsignedTinyInteger('procedure_score_component')->default(0)->after('landing_score_component');

            // Takeoff milestone
            $table->unsignedInteger('takeoff_weight')->nullable()->after('procedure_score_component');
            $table->unsignedTinyInteger('takeoff_flaps')->nullable();
            $table->decimal('departure_heading_dev', 4, 1)->nullable();
            $table->decimal('departure_centerline_dev', 5, 1)->nullable();
            $table->decimal('departure_lat', 9, 6)->nullable();
            $table->decimal('departure_lon', 9, 6)->nullable();

            // Landing milestone
            $table->unsignedInteger('landing_weight')->nullable();
            $table->smallInteger('landing_rate')->nullable();
            $table->decimal('landing_g_force', 4, 2)->nullable();
            $table->decimal('landing_pitch', 4, 1)->nullable();
            $table->decimal('landing_bank', 4, 1)->nullable();
            $table->unsignedSmallInteger('landing_speed_ias')->nullable();
            $table->unsignedTinyInteger('landing_flaps')->nullable();
            $table->decimal('touchdown_lat', 9, 6)->nullable();
            $table->decimal('touchdown_lon', 9, 6)->nullable();

            // Arrival milestone
            $table->unsignedSmallInteger('arrival_threshold_dist')->nullable();
            $table->decimal('arrival_centerline_dev', 5, 1)->nullable();
            $table->decimal('arrival_heading_dev', 4, 1)->nullable();

            // Integrity flags
            $table->boolean('slew_used')->default(false);
            $table->boolean('sim_rate_exceeded')->default(false);
            $table->boolean('crashed')->default(false);
            $table->boolean('unlimited_fuel')->default(false);
            $table->unsignedSmallInteger('total_pause_min')->default(0);

            // Fuel
            $table->unsignedInteger('landing_fuel')->nullable();

            // Scoring audit
            $table->json('scoring_snapshot')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('hun_fdm_reports', function (Blueprint $table) {
            $table->dropColumn([
                'landing_score_component', 'procedure_score_component',
                'takeoff_weight', 'takeoff_flaps',
                'departure_heading_dev', 'departure_centerline_dev',
                'departure_lat', 'departure_lon',
                'landing_weight', 'landing_rate', 'landing_g_force',
                'landing_pitch', 'landing_bank', 'landing_speed_ias',
                'landing_flaps', 'touchdown_lat', 'touchdown_lon',
                'arrival_threshold_dist', 'arrival_centerline_dev', 'arrival_heading_dev',
                'slew_used', 'sim_rate_exceeded', 'crashed', 'unlimited_fuel',
                'total_pause_min', 'landing_fuel', 'scoring_snapshot',
            ]);
        });
    }
};

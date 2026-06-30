<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hun_fdm_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pirep_id')->nullable()->index();
            $table->string('flight_id')->nullable();
            $table->string('aircraft_icao', 20)->nullable();
            $table->string('flight_profile', 20)->nullable();

            // Scores
            $table->unsignedTinyInteger('landing_score')->default(100);
            $table->unsignedSmallInteger('fdm_deductions')->default(0);
            $table->unsignedTinyInteger('final_score')->default(100);

            // Exceedance summary
            $table->unsignedTinyInteger('exceedance_count')->default(0);
            $table->unsignedTinyInteger('critical_count')->default(0);
            $table->unsignedSmallInteger('flight_duration_min')->default(0);

            // Full exceedance log (JSON array)
            $table->json('exceedances')->nullable();

            $table->string('recorded_at')->nullable();
            $table->timestamps();

            $table->foreign('pirep_id')
                  ->references('id')
                  ->on('pireps')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hun_fdm_reports');
    }
};

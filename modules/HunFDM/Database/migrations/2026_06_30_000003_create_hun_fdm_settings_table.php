<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hun_fdm_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 80)->unique();
            $table->text('value')->nullable();
            $table->string('type', 20)->default('string'); // string|boolean|integer|float|json
            $table->string('label', 120)->nullable();
            $table->string('group', 40)->nullable(); // scoring|phases|thresholds|repeat
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hun_fdm_settings');
    }
};

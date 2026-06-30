<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hun_fdm_rules', function (Blueprint $table) {
            $table->id();
            $table->string('key', 60)->unique();
            $table->string('category', 30);
            $table->string('label', 120);
            $table->boolean('enabled')->default(true);
            $table->unsignedTinyInteger('deduction')->default(0);
            $table->boolean('critical')->default(false);
            $table->unsignedSmallInteger('cooldown_sec')->default(10);
            $table->boolean('once_per_flight')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hun_fdm_rules');
    }
};

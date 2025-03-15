<?php

use App\Models\TemperatureHumidity;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('temperature_deviations', function (Blueprint $table) {
            $table->id();
            $table->uuid('temperatureDeviationId')->unique();
            $table->foreignIdFor(TemperatureHumidity::class, 'temperature_id');
            $table->date('date');
            $table->time('time');
            $table->decimal('temperature_deviation', 5, 2);
            $table->integer('length_temperature_deviation');
            $table->string('deviation_reason');
            $table->string('pic');
            $table->string('risk_analysis');
            $table->string('analyzer_pic');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temperature_deviations');
    }
};

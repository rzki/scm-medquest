<?php

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
        Schema::create('temperatures_humidities', function (Blueprint $table) {
            $table->id();
            $table->uuid('temperatureId')->unique();
            $table->date('date');
            $table->date('period');
            $table->string('location');
            $table->string('serial_no');
            $table->integer('observed_temperature_start');
            $table->integer('observed_temperature_end');
            $table->time('time_0800')->nullable();
            $table->decimal('temp_0800', 5, 2)->nullable();
            $table->integer('rh_0800')->nullable();
            $table->string('pic_0800')->nullable();
            $table->time('time_1100')->nullable();
            $table->decimal('temp_1100', 5, 2)->nullable();
            $table->integer('rh_1100')->nullable();
            $table->string('pic_1100')->nullable();
            $table->time('time_1400')->nullable();
            $table->decimal('temp_1400', 5, 2)->nullable();
            $table->integer('rh_1400')->nullable();
            $table->string('pic_1400')->nullable();
            $table->time('time_1700')->nullable();
            $table->decimal('temp_1700', 5, 2)->nullable();
            $table->integer('rh_1700')->nullable();
            $table->string('pic_1700')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temperature_humidities');
    }
};

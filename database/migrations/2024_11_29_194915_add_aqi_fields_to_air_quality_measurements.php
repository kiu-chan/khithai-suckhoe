<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAqiFieldsToAirQualityMeasurements extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('air_quality_measurements', function (Blueprint $table) {
            $table->float('aqi')->nullable()->after('tsp_level');
            $table->string('aqi_status', 50)->nullable()->after('aqi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('air_quality_measurements', function (Blueprint $table) {
            $table->dropColumn(['aqi', 'aqi_status']);
        });
    }
}
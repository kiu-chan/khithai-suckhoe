<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('air_quality_measurements', function (Blueprint $table) {
            $table->foreignId('monitoring_station_id')->nullable()->after('factory_id')
                  ->constrained('monitoring_stations')->nullOnDelete();
            
            // Thêm index cho cột mới
            $table->index('monitoring_station_id');
        });
    }

    public function down()
    {
        Schema::table('air_quality_measurements', function (Blueprint $table) {
            $table->dropForeign(['monitoring_station_id']);
            $table->dropColumn('monitoring_station_id');
        });
    }
};
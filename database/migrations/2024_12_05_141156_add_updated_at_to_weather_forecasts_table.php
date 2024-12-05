<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUpdatedAtToWeatherForecastsTable extends Migration
{
    public function up()
    {
        Schema::table('weather_forecasts', function (Blueprint $table) {
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::table('weather_forecasts', function (Blueprint $table) {
            $table->dropColumn('updated_at');
        });
    }
}
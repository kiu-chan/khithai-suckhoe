<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

class CreateMapThaiNguyenTable extends Migration
{
    public function up()
    {
        DB::statement('CREATE TABLE map_thai_nguyen (
            id SERIAL PRIMARY KEY,
            name VARCHAR(255),
            geom geometry(MULTIPOLYGON, 4326)
        )');
    }

    public function down()
    {
        Schema::dropIfExists('map_thai_nguyen');
    }
}
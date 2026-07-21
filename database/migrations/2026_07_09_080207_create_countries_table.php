<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // up
    public function up()
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 3)->unique();
            $table->string('capital')->nullable();
            $table->string('currency')->nullable();
            $table->string('currency_symbol')->nullable();
            $table->decimal('gdp', 20, 2)->nullable();
            $table->decimal('inflation', 10, 2)->nullable();
            $table->decimal('population', 15, 2)->nullable();
            $table->string('flag_url')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->json('weather_data')->nullable();
            $table->json('exchange_rate_data')->nullable();
            $table->timestamps();
        });
    }

    // down
    public function down()
    {
        Schema::dropIfExists('countries');
    }
};
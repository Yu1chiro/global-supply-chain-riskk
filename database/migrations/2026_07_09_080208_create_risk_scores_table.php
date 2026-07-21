<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // up
    public function up()
    {
        Schema::create('risk_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained()->onDelete('cascade');
            $table->decimal('weather_risk', 5, 2)->default(0);
            $table->decimal('inflation_risk', 5, 2)->default(0);
            $table->decimal('currency_risk', 5, 2)->default(0);
            $table->decimal('political_risk', 5, 2)->default(0);
            $table->decimal('news_sentiment_risk', 5, 2)->default(0);
            $table->decimal('total_risk', 5, 2)->default(0);
            $table->string('risk_level')->nullable(); 
            $table->json('raw_data')->nullable();
            $table->timestamp('calculated_at');
            $table->timestamps();

            $table->index(['country_id', 'calculated_at']);
        });
    }

    // down
    public function down()
    {
        Schema::dropIfExists('risk_scores');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // up
    public function up()
    {
        Schema::create('news_cache', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('content')->nullable();
            $table->string('url')->nullable();
            $table->string('source')->nullable();
            $table->string('image_url')->nullable();
            $table->string('category')->nullable();
            $table->foreignId('country_id')->nullable()->constrained()->onDelete('set null');
            $table->json('sentiment_data')->nullable();
            $table->string('sentiment_result')->nullable(); 
            $table->timestamp('published_at');
            $table->timestamps();

            $table->index(['country_id', 'sentiment_result']);
        });
    }

    // down
    public function down()
    {
        Schema::dropIfExists('news_cache');
    }
};
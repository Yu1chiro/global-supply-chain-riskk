<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // up
    public function up()
    {
        Schema::create('positive_words', function (Blueprint $table) {
            $table->id();
            $table->string('word')->unique();
            $table->integer('weight')->default(1);
            $table->timestamps();
        });

        Schema::create('negative_words', function (Blueprint $table) {
            $table->id();
            $table->string('word')->unique();
            $table->integer('weight')->default(1);
            $table->timestamps();
        });
    }

    // down
    public function down()
    {
        Schema::dropIfExists('positive_words');
        Schema::dropIfExists('negative_words');
    }
};
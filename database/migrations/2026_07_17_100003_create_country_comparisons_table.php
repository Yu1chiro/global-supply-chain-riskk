<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    // up
    public function up()
    {
        Schema::create('country_comparisons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('country_a_id')->constrained('countries')->onDelete('cascade');
            $table->foreignId('country_b_id')->constrained('countries')->onDelete('cascade');
            $table->json('comparison_result')->nullable();
            $table->timestamps();

            $table->index(['country_a_id', 'country_b_id']);
        });
    }

    // down
    public function down()
    {
        Schema::dropIfExists('country_comparisons');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    // up
    public function up()
    {
        Schema::create('port_traffic_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('port_id')->constrained()->onDelete('cascade');
            $table->enum('congestion_level', ['low', 'moderate', 'high', 'severe'])->default('low');
            $table->integer('vessels_waiting')->nullable();
            $table->integer('average_delay_hours')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->index(['port_id', 'recorded_at']);
        });
    }

    // down
    public function down()
    {
        Schema::dropIfExists('port_traffic_logs');
    }
};

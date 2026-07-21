<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    // up
    public function up()
    {
        Schema::create('api_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->string('api_name'); 
            $table->string('endpoint')->nullable();
            $table->enum('status', ['success', 'failed', 'timeout'])->default('success');
            $table->integer('response_time_ms')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('synced_at');
            $table->timestamps();

            $table->index(['api_name', 'synced_at']);
            $table->index('status');
        });
    }

    // down
    public function down()
    {
        Schema::dropIfExists('api_sync_logs');
    }
};

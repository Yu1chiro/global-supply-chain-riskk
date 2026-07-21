<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    // up
    public function up()
    {
        Schema::create('currency_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained()->onDelete('cascade');
            $table->string('currency_code', 10);
            $table->decimal('rate_to_usd', 20, 6);
            $table->date('rate_date');
            $table->timestamps();

            $table->unique(['country_id', 'currency_code', 'rate_date']);
            $table->index(['currency_code', 'rate_date']);
        });
    }

    // down
    public function down()
    {
        Schema::dropIfExists('currency_rates');
    }
};

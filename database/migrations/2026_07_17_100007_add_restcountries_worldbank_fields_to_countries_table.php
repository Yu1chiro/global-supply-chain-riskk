<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // up
    public function up()
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->string('region')->nullable()->after('capital');
            $table->string('subregion')->nullable()->after('region');
            $table->json('languages')->nullable()->after('currency_symbol');
            $table->decimal('exports', 20, 2)->nullable()->after('inflation');
            $table->decimal('imports', 20, 2)->nullable()->after('exports');
            $table->timestamp('external_synced_at')->nullable()->after('exchange_rate_data');
        });
    }

    // down
    public function down()
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->dropColumn(['region', 'subregion', 'languages', 'exports', 'imports', 'external_synced_at']);
        });
    }
};

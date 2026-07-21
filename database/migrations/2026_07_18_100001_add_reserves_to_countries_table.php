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
            $table->decimal('reserves', 20, 2)->nullable()->after('imports');
        });
    }

    // down
    public function down()
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->dropColumn('reserves');
        });
    }
};

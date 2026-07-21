<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // up
    public function up()
    {
        DB::table('news_cache')->where('url', 'like', 'https://example.com/%')->delete();
    }

    // down
    public function down()
    {
        
    }
};

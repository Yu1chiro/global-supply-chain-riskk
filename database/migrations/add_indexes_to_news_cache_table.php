<?php
// database/migrations/2026_07_21_111035_add_indexes_to_news_cache_table.php
//
// PERFORMANCE FIX: halaman News (GET /api/news?category=...) melakukan
// `WHERE category = ? ORDER BY published_at DESC` + paginate() (yang perlu
// COUNT total baris juga). Migration asli tabel ini cuma bikin index untuk
// (country_id, sentiment_result) — TIDAK ADA index untuk `category` atau
// `published_at` sama sekali. Begitu tabel news_cache mulai terisi banyak
// baris (dari sync berkala + berbagai percobaan fetch sebelumnya), query
// ini jadi full table scan setiap kali halaman News dibuka -> lambat.
//
// Index composite (category, published_at) di bawah ini pas banget dengan
// pola query index()/GET /api/news: MySQL bisa langsung pakai index ini
// untuk filter category SEKALIGUS sudah terurut published_at DESC, tanpa
// perlu scan+sort manual. Index published_at sendiri juga ditambah untuk
// query yang tidak filter category (mis. widget "Latest News" di Dashboard).

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('news_cache', function (Blueprint $table) {
            $table->index(['category', 'published_at'], 'news_cache_category_published_at_index');
            $table->index('published_at', 'news_cache_published_at_index');
            $table->index('url', 'news_cache_url_index');
        });
    }

    public function down()
    {
        Schema::table('news_cache', function (Blueprint $table) {
            $table->dropIndex('news_cache_category_published_at_index');
            $table->dropIndex('news_cache_published_at_index');
            $table->dropIndex('news_cache_url_index');
        });
    }
};

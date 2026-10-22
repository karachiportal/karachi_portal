<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Price items: price_category_id → set null on delete
        Schema::table('price_items', function (Blueprint $table) {
            $table->dropForeign(['price_category_id']);
        });
        Schema::table('price_items', function (Blueprint $table) {
            $table->foreignId('price_category_id')->nullable()->change();
            $table->foreign('price_category_id')
                  ->references('id')->on('price_categories')
                  ->onDelete('set null');
        });

        // Price histories: price_item_id → set null on delete
        Schema::table('price_histories', function (Blueprint $table) {
            $table->dropForeign(['price_item_id']);
        });
        Schema::table('price_histories', function (Blueprint $table) {
            $table->foreignId('price_item_id')->nullable()->change();
            $table->foreign('price_item_id')
                  ->references('id')->on('price_items')
                  ->onDelete('set null');
        });

        // Price update logs: price_item_id → set null on delete
        Schema::table('price_update_logs', function (Blueprint $table) {
            $table->dropForeign(['price_item_id']);
        });
        Schema::table('price_update_logs', function (Blueprint $table) {
            $table->foreignId('price_item_id')->nullable()->change();
            $table->foreign('price_item_id')
                  ->references('id')->on('price_items')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        // Restore price_items FK
        Schema::table('price_items', function (Blueprint $table) {
            $table->dropForeign(['price_category_id']);
        });
        Schema::table('price_items', function (Blueprint $table) {
            $table->foreignId('price_category_id')->nullable(false)->change();
            $table->foreign('price_category_id')
                  ->references('id')->on('price_categories')
                  ->onDelete('cascade');
        });

        // Restore price_histories FK
        Schema::table('price_histories', function (Blueprint $table) {
            $table->dropForeign(['price_item_id']);
        });
        Schema::table('price_histories', function (Blueprint $table) {
            $table->foreignId('price_item_id')->nullable(false)->change();
            $table->foreign('price_item_id')
                  ->references('id')->on('price_items')
                  ->onDelete('cascade');
        });

        // Restore price_update_logs FK
        Schema::table('price_update_logs', function (Blueprint $table) {
            $table->dropForeign(['price_item_id']);
        });
        Schema::table('price_update_logs', function (Blueprint $table) {
            $table->foreignId('price_item_id')->nullable(false)->change();
            $table->foreign('price_item_id')
                  ->references('id')->on('price_items')
                  ->onDelete('cascade');
        });
    }
};

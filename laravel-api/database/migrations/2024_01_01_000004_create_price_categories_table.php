<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('price_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('name_urdu', 100)->nullable();
            $table->string('slug', 50)->unique();
            $table->string('icon', 255)->nullable()->default('📦');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('price_categories'); }
};

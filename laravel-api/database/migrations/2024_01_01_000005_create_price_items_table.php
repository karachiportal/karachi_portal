<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('price_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('price_category_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('name_urdu', 100)->nullable();
            $table->string('unit', 20)->default('1 Kg');
            $table->string('unit_urdu', 20)->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('previous_price', 10, 2)->default(0);
            $table->decimal('price_change', 10, 2)->default(0);
            $table->decimal('change_percent', 8, 2)->default(0);
            $table->string('image_url')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['price_category_id', 'is_active']);
        });
    }
    public function down(): void { Schema::dropIfExists('price_items'); }
};

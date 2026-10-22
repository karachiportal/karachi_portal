<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('price_update_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('price_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('old_price', 10, 2);
            $table->decimal('new_price', 10, 2);
            $table->decimal('change', 10, 2);
            $table->decimal('change_percent', 8, 2);
            $table->string('source', 20)->default('single'); // single, bulk
            $table->timestamps();
            $table->index(['price_item_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_update_logs');
    }
};

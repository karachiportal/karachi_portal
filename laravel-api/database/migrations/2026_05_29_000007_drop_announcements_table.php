<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('announcements');
    }
    public function down(): void
    {
        // Irreversible — table re-creation not needed
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('downloads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('reference_id')->constrained('references')->onDelete('cascade');
            $table->timestamp('downloaded_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('downloads');
    }
};
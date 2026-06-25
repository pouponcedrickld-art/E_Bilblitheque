<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('references', function (Blueprint $table) {
            $table->boolean('allow_download')->default(true)->after('file_path');
            $table->unsignedBigInteger('file_size')->nullable()->after('allow_download');
        });
    }

    public function down(): void
    {
        Schema::table('references', function (Blueprint $table) {
            $table->dropColumn(['allow_download', 'file_size']);
        });
    }
};

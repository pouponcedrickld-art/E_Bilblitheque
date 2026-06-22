<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    // Crée la table 'references'
    public function up(): void
    {
        Schema::create('references', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->text('abstract')->nullable();
            $table->string('isbn')->nullable();
            $table->year('publication_year')->nullable();
            $table->enum('language', ['fr', 'en', 'autre'])->default('fr');
            $table->enum('document_type', ['livre', 'memoire', 'these', 'article', 'revue', 'rapport', 'guide', 'autre'])->default('autre');
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->foreignId('publisher_id')->nullable()->constrained('publishers')->onDelete('set null');
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            $table->string('cover_image')->nullable();
            $table->string('file_path')->nullable();
            $table->integer('pages')->nullable();
            $table->unsignedBigInteger('download_count')->default(0);
            $table->unsignedBigInteger('view_count')->default(0);
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('references');
    }
};
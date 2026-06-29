<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('deposit_requests', function (Blueprint $table) {
            $table->string('subtitle')->nullable()->after('title');
            $table->text('abstract')->nullable()->after('subtitle');
            $table->string('isbn')->nullable()->after('abstract');
            $table->year('publication_year')->nullable()->after('isbn');
            $table->enum('language', ['fr', 'en', 'autre'])->default('fr')->after('publication_year');
            $table->enum('document_type', ['livre', 'memoire', 'these', 'article', 'revue', 'rapport', 'guide', 'autre'])->default('autre')->after('language');
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete()->after('document_type');
            $table->foreignId('publisher_id')->nullable()->constrained('publishers')->nullOnDelete()->after('category_id');
            $table->integer('pages')->nullable()->after('publisher_id');
            $table->string('cover_image')->nullable()->after('proposed_file');
        });
    }

    public function down(): void
    {
        Schema::table('deposit_requests', function (Blueprint $table) {
            $table->dropColumn([
                'subtitle', 'abstract', 'isbn', 'publication_year',
                'language', 'document_type', 'category_id', 'publisher_id',
                'pages', 'cover_image',
            ]);
        });
    }
};

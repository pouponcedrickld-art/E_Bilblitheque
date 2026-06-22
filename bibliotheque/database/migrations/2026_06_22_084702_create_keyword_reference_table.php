<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Crée la table pivot 'keyword_reference' et migre les anciens mots-clés depuis 'reference_keywords'
    public function up(): void
    {
        Schema::create('keyword_reference', function (Blueprint $table) {
            $table->foreignId('keyword_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reference_id')->constrained()->cascadeOnDelete();
            $table->primary(['keyword_id', 'reference_id']);
        });

        // Migrate existing keywords from reference_keywords to the new structure
        $oldKeywords = DB::table('reference_keywords')->get();
        foreach ($oldKeywords as $old) {
            $keywordId = DB::table('keywords')->where('name', $old->keyword)->value('id');
            if (!$keywordId) {
                $slug = \Illuminate\Support\Str::slug($old->keyword);
                $keywordId = DB::table('keywords')->insertGetId([
                    'name' => $old->keyword,
                    'slug' => $slug,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            DB::table('keyword_reference')->insertOrIgnore([
                'keyword_id' => $keywordId,
                'reference_id' => $old->reference_id,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('keyword_reference');
    }
};

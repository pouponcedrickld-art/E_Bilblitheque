<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $mapping = [
            'article' => 1,
            'livre' => 2,
            'memoire' => 3,
            'these' => 4,
            'revue' => 5,
            'rapport' => 6,
            'guide' => 7,
            'autre' => 8,
        ];

        // --- references ---
        Schema::table('references', function (Blueprint $table) {
            $table->unsignedBigInteger('document_type_id')->nullable();
        });

        foreach ($mapping as $old => $new) {
            DB::table('references')->where('document_type', $old)->update(['document_type_id' => $new]);
        }
        DB::table('references')->whereNull('document_type_id')->update(['document_type_id' => 8]);

        Schema::table('references', function (Blueprint $table) {
            $table->dropColumn('document_type');
            $table->foreign('document_type_id')->references('id')->on('document_types')->onDelete('set null');
        });

        // --- deposit_requests ---
        Schema::table('deposit_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('document_type_id')->nullable();
        });

        foreach ($mapping as $old => $new) {
            DB::table('deposit_requests')->where('document_type', $old)->update(['document_type_id' => $new]);
        }
        DB::table('deposit_requests')->whereNull('document_type_id')->update(['document_type_id' => 8]);

        Schema::table('deposit_requests', function (Blueprint $table) {
            $table->dropColumn('document_type');
            $table->foreign('document_type_id')->references('id')->on('document_types')->onDelete('set null');
        });
    }

    public function down(): void
    {
        // --- references ---
        Schema::table('references', function (Blueprint $table) {
            $table->string('document_type', 50)->nullable();
        });

        $this->restoreDocumentType('references');

        Schema::table('references', function (Blueprint $table) {
            $table->dropForeign(['document_type_id']);
            $table->dropColumn('document_type_id');
        });

        // --- deposit_requests ---
        Schema::table('deposit_requests', function (Blueprint $table) {
            $table->string('document_type', 50)->nullable();
        });

        $this->restoreDocumentType('deposit_requests');

        Schema::table('deposit_requests', function (Blueprint $table) {
            $table->dropForeign(['document_type_id']);
            $table->dropColumn('document_type_id');
        });
    }

    private function restoreDocumentType(string $table): void
    {
        DB::statement("
            UPDATE $table
            SET document_type = CASE document_type_id
                WHEN 1 THEN 'article'
                WHEN 2 THEN 'livre'
                WHEN 3 THEN 'memoire'
                WHEN 4 THEN 'these'
                WHEN 5 THEN 'revue'
                WHEN 6 THEN 'rapport'
                WHEN 7 THEN 'guide'
                ELSE 'autre'
            END
        ");
    }
};

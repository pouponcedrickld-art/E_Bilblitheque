<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Index pour les notifications (filtrées par user_id, triées par created_at)
        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['user_id', 'created_at']);
            $table->index(['user_id', 'is_read']);
        });

        // Index pour les logs d'activité (filtrés par user_id, action, target_table, triés par created_at)
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('action');
            $table->index('target_table');
            $table->index('created_at');
        });

        // Index pour les téléchargements
        Schema::table('downloads', function (Blueprint $table) {
            $table->index('reference_id');
            $table->index('downloaded_at');
        });

        // Index pour les consultations
        Schema::table('views', function (Blueprint $table) {
            $table->index('reference_id');
            $table->index('viewed_at');
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'created_at']);
            $table->dropIndex(['user_id', 'is_read']);
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['action']);
            $table->dropIndex(['target_table']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('downloads', function (Blueprint $table) {
            $table->dropIndex(['reference_id']);
            $table->dropIndex(['downloaded_at']);
        });

        Schema::table('views', function (Blueprint $table) {
            $table->dropIndex(['reference_id']);
            $table->dropIndex(['viewed_at']);
        });
    }
};

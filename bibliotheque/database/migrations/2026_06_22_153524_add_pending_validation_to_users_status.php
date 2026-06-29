<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN status ENUM('active','inactive','suspended','pending_validation') NOT NULL DEFAULT 'pending_validation'");
        }
    }

    public function down(): void
    {
        DB::table('users')->where('status', 'pending_validation')->update(['status' => 'active']);
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN status ENUM('active','inactive','suspended') NOT NULL DEFAULT 'active'");
        }
    }
};

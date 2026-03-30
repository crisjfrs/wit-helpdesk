<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ubah kolom category menjadi nullable untuk menghindari error ENUM
        // Karena sekarang kita menggunakan category_id saja
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            // SQLite tidak support ENUM dan MODIFY COLUMN, jadi ubah ke string nullable
            Schema::table('tickets', function (Blueprint $table) {
                $table->string('category')->nullable()->change();
            });
        } else {
            // MySQL/other: tetap gunakan ENUM
            DB::statement("ALTER TABLE tickets MODIFY COLUMN category ENUM('hardware', 'software', 'network', 'other') NULL DEFAULT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            Schema::table('tickets', function (Blueprint $table) {
                $table->string('category')->default('other')->nullable(false)->change();
            });
        } else {
            DB::statement("ALTER TABLE tickets MODIFY COLUMN category ENUM('hardware', 'software', 'network', 'other') NOT NULL DEFAULT 'other'");
        }
    }
};

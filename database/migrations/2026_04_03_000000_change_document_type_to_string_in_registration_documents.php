<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Use raw SQL to quickly bypass formatting issues with ENUM in Laravel without doctrine/dbal
        DB::statement("ALTER TABLE registration_documents MODIFY COLUMN document_type VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE registration_documents MODIFY COLUMN document_type ENUM('CR', 'DL', 'SID') COLLATE utf8mb4_unicode_ci NOT NULL");
    }
};

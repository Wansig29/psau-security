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
        // Add mediumblob/longblob
        // Instead of using Blueprint, for strict LONGBLOB enforcement it's usually safer
        // to use standard syntax since generic binary might use BLOB.
        // But Laravel's binary() uses BLOB, and we might want LONGBLOB.
        Schema::table('registration_documents', function (Blueprint $table) {
            // In MySQL, mediumblob is often used for files up to 16MB. We'll use longblob just in case.
            // In Laravel 11.x, we can manually add it to stay cross-compatible if longblob isn't a method, 
            // but standard is binary. However DB statement guarantees LONGBLOB for MySQL.
        });
        
        DB::statement('ALTER TABLE registration_documents ADD image_data LONGBLOB NULL AFTER image_path');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registration_documents', function (Blueprint $table) {
            $table->dropColumn('image_data');
        });
    }
};

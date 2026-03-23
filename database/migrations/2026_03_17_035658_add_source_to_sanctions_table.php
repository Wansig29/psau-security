<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sanctions', function (Blueprint $table) {
            $table->enum('source', ['auto', 'manual'])->default('manual')->after('is_active');
            $table->string('description')->nullable()->after('source');
        });
    }

    public function down(): void
    {
        Schema::table('sanctions', function (Blueprint $table) {
            $table->dropColumn(['source', 'description']);
        });
    }
};

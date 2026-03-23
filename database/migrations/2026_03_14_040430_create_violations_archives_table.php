<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('violations_archives', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('original_violation_id');
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');
            $table->string('school_year');
            $table->string('violation_type');
            $table->text('location_notes')->nullable();
            $table->decimal('gps_lat', 10, 8)->nullable();
            $table->decimal('gps_lng', 11, 8)->nullable();
            $table->boolean('sanction_applied')->default(false);
            $table->foreignId('logged_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('archived_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('violations_archives');
    }
};

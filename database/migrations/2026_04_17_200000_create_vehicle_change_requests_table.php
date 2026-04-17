<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_change_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('old_vehicle_id')->nullable()->constrained('vehicles')->onDelete('set null');
            $table->foreignId('old_registration_id')->nullable()->constrained('registrations')->onDelete('set null');

            // New vehicle info supplied by user
            $table->string('new_make');
            $table->string('new_model');
            $table->string('new_color');
            $table->string('new_plate_number')->nullable(); // extracted by OCR, may be null
            $table->text('reason')->nullable();

            // Document paths stored as JSON array keyed by type
            $table->json('document_paths')->nullable();
            $table->json('image_data')->nullable(); // binary blobs stored as base64

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_change_requests');
    }
};

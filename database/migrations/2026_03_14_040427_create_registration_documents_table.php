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
        Schema::create('registration_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained('registrations')->onDelete('cascade');
            $table->enum('document_type', ['CR', 'DL', 'SID']);
            $table->string('image_path')->nullable(); // deleted after approval
            $table->text('ocr_extracted_text')->nullable(); // kept permanently
            $table->decimal('match_score', 5, 2)->nullable();
            $table->boolean('is_flagged')->default(false);
            $table->json('flagged_fields')->nullable(); // array of fields that mismatched
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registration_documents');
    }
};

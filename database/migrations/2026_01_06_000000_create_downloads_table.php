<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('downloads', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Display name
            $table->string('original_filename'); // Original uploaded filename
            $table->string('file_type'); // MIME type
            $table->string('file_extension'); // File extension
            $table->bigInteger('file_size')->default(0); // File size in bytes
            $table->string('storage_path')->nullable(); // Path in Google Storage
            $table->string('storage_url')->nullable(); // Public URL from Google Storage
            $table->enum('status', ['pending', 'uploading', 'completed', 'failed'])->default('pending');
            $table->integer('upload_progress')->default(0); // 0-100 percentage
            $table->integer('download_count')->default(0);
            $table->text('error_message')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('downloads');
    }
};

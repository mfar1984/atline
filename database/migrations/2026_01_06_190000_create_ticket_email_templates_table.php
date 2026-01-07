<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique(); // e.g., 'ticket_assigned_to_you', 'new_customer_reply'
            $table->string('title'); // Display title
            $table->string('description'); // Short description
            $table->enum('recipient_type', ['client', 'staff', 'admin']); // Who receives this email
            $table->string('subject'); // Email subject line
            $table->text('content'); // Email body content with placeholders
            $table->boolean('is_active')->default(true);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_email_templates');
    }
};

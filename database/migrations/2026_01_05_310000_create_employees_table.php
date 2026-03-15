<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            
            // Page 1: Employee Info
            $table->string('full_name');
            $table->string('ic_number')->unique();
            $table->date('birthday')->nullable();
            
            // Current Address
            $table->string('current_address_1')->nullable();
            $table->string('current_address_2')->nullable();
            $table->string('current_postcode')->nullable();
            $table->string('current_district')->nullable();
            $table->string('current_state')->nullable();
            $table->string('current_country')->default('Malaysia');
            
            // Correspondence Address
            $table->string('correspondence_address_1')->nullable();
            $table->string('correspondence_address_2')->nullable();
            $table->string('correspondence_postcode')->nullable();
            $table->string('correspondence_district')->nullable();
            $table->string('correspondence_state')->nullable();
            $table->string('correspondence_country')->default('Malaysia');
            
            // Page 2: Contact Information
            $table->string('telephone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('email')->nullable();
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed'])->default('single');
            
            // Emergency Contact
            $table->string('emergency_name')->nullable();
            $table->string('emergency_telephone')->nullable();
            $table->string('emergency_relationship')->nullable();
            
            // Page 4: Staff Data
            $table->decimal('salary', 12, 2)->nullable();
            $table->string('position')->nullable();
            $table->date('join_date')->nullable();
            $table->string('time_works')->nullable(); // e.g., "9:00 AM - 6:00 PM"
            
            $table->enum('status', ['active', 'inactive', 'resigned'])->default('active');
            $table->timestamps();
        });

        // Page 3: Education (Multiple)
        Schema::create('employee_educations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('level'); // SPM, STPM, Diploma, Degree, Master, PhD
            $table->string('institution');
            $table->string('field_of_study')->nullable();
            $table->year('year_start')->nullable();
            $table->year('year_end')->nullable();
            $table->string('grade')->nullable();
            $table->timestamps();
        });

        // Page 5: Attachments
        Schema::create('employee_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['front_ic', 'back_ic', 'resume', 'certificate', 'offer_letter', 'other']);
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type')->nullable();
            $table->integer('file_size')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_attachments');
        Schema::dropIfExists('employee_educations');
        Schema::dropIfExists('employees');
    }
};

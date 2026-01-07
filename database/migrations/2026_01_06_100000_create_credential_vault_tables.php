<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // User vault keys table - stores encrypted MEK and PIN info
        Schema::create('user_vault_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->text('encrypted_mek');
            $table->string('mek_iv', 32);
            $table->string('pin_hash', 255);
            $table->string('pin_salt', 32);
            $table->timestamp('pin_expires_at');
            $table->integer('failed_attempts')->default(0);
            $table->timestamp('locked_until')->nullable();
            $table->boolean('is_initialized')->default(false);
            $table->timestamps();
        });

        // Credentials table - stores encrypted credential data
        Schema::create('credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 255);
            $table->enum('type', ['ssh', 'windows', 'license_key', 'database', 'api_key', 'other']);
            $table->text('encrypted_data');
            $table->string('data_iv', 32);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'type']);
        });

        // Audit logs table - tracks credential access
        Schema::create('credential_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('credential_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('action', ['unlock', 'view', 'create', 'update', 'delete', 'pin_rotate']);
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'action', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credential_audit_logs');
        Schema::dropIfExists('credentials');
        Schema::dropIfExists('user_vault_keys');
    }
};

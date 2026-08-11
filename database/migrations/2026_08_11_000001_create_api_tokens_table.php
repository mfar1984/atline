<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tokens for the External read-only API (/api/v1/*).
 *
 * The plaintext token is shown once at creation and never stored — only a
 * SHA-256 hash is kept, so a database leak cannot be replayed against the API.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->char('token_hash', 64)->unique()->comment('SHA-256 of the plaintext token');
            $table->string('abilities', 255)->default('external:read')->comment('Comma separated');
            $table->timestamp('last_used_at')->nullable();
            $table->string('last_used_ip', 45)->nullable();
            $table->timestamp('expires_at')->nullable()->comment('NULL = never expires');
            $table->timestamp('revoked_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['revoked_at', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_tokens');
    }
};

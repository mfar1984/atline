<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_assignees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['ticket_id', 'user_id']);
        });
        
        // Migrate existing assigned_to data to new table
        $tickets = DB::table('tickets')->whereNotNull('assigned_to')->get();
        foreach ($tickets as $ticket) {
            DB::table('ticket_assignees')->insert([
                'ticket_id' => $ticket->id,
                'user_id' => $ticket->assigned_to,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_assignees');
    }
};

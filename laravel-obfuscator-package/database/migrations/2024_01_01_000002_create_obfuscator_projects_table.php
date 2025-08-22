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
        Schema::create('obfuscator_projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('user_id')->constrained('obfuscator_users')->onDelete('cascade');
            $table->enum('status', ['active', 'archived', 'deleted'])->default('active');
            $table->json('settings')->nullable();
            $table->bigInteger('storage_used')->default(0);
            $table->integer('file_count')->default(0);
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('obfuscator_projects');
    }
};

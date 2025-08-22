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
        Schema::create('obfuscator_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('obfuscator_users')->onDelete('cascade');
            $table->foreignId('project_id')->nullable()->constrained('obfuscator_projects')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['single', 'batch', 'directory', 'scheduled'])->default('single');
            $table->enum('status', ['pending', 'running', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->json('input_data');
            $table->json('output_data')->nullable();
            $table->json('settings')->nullable();
            $table->string('cron_expression')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->integer('progress')->default(0);
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index(['project_id', 'status']);
            $table->index(['type', 'status']);
            $table->index('status');
            $table->index('scheduled_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('obfuscator_jobs');
    }
};

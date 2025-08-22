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
        Schema::create('obfuscator_project_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('obfuscator_projects')->onDelete('cascade');
            $table->string('original_path');
            $table->string('obfuscated_path')->nullable();
            $table->string('backup_path')->nullable();
            $table->string('filename');
            $table->string('file_type');
            $table->bigInteger('file_size');
            $table->enum('status', ['pending', 'obfuscated', 'failed', 'restored'])->default('pending');
            $table->json('obfuscation_settings')->nullable();
            $table->timestamp('obfuscated_at')->nullable();
            $table->timestamps();
            
            $table->index(['project_id', 'status']);
            $table->index('status');
            $table->index('file_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('obfuscator_project_files');
    }
};

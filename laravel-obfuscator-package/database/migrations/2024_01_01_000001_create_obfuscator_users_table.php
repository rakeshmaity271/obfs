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
        Schema::create('obfuscator_users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('role', ['admin', 'developer', 'user'])->default('user');
            $table->boolean('is_active')->default(true);
            $table->string('api_key', 64)->unique()->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->json('preferences')->nullable();
            $table->timestamps();
            
            $table->index(['role', 'is_active']);
            $table->index('api_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('obfuscator_users');
    }
};

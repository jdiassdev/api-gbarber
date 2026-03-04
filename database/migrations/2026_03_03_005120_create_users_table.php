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
        Schema::create('users', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->string('name', 100);
            $table->string('email', 100)->unique();
            $table->string('cpf', 11)->unique()->nullable();

            $table->string('password');

            $table->date('birthday')->nullable();
            $table->string('phone', 20)->nullable();

            $table->boolean('is_active')->default(true);
            $table->string('role', 20)->default('client');

            $table->timestamps();

            $table->index('role');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};

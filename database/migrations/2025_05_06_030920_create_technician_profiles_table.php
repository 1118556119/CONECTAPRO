<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('technician_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Relación con users
            $table->string('specialty')->nullable(); // Especialidad del técnico
            $table->integer('experience')->nullable(); // Años de experiencia
            $table->text('bio')->nullable(); // Biografía profesional
            $table->string('photo_url')->nullable(); // Foto de perfil
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('technician_profiles');
    }
};
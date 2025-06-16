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
        Schema::create('technician_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Información profesional básica
            $table->string('specialty')->nullable(); // Especialidad técnica
            $table->integer('experience_years')->default(0); // Años de experiencia
            $table->text('bio')->nullable(); // Biografía/descripción
            $table->string('profile_photo')->nullable(); // Foto de perfil
            
            // Educación y formación
            $table->string('education_level')->nullable(); // Nivel educativo
            $table->string('institution')->nullable(); // Institución educativa
            $table->string('degree')->nullable(); // Título obtenido
            $table->year('graduation_year')->nullable();
            
            // Certificaciones y habilidades
            $table->json('certifications')->nullable(); // Certificaciones técnicas
            $table->json('skills')->nullable(); // Habilidades técnicas
            $table->json('languages')->nullable(); // Idiomas
            
            // Información laboral
            $table->string('current_company')->nullable();
            $table->string('current_position')->nullable();
            $table->decimal('hourly_rate', 10, 2)->nullable(); // Tarifa por hora
            
            // Estado y disponibilidad
            $table->boolean('is_verified')->default(false); // Verificado por admin
            $table->boolean('is_available')->default(true); // Disponible para trabajos
            $table->json('service_areas')->nullable(); // Áreas de servicio (ciudades)
            $table->json('availability_schedule')->nullable(); // Horarios disponibles
            
            // Métricas
            $table->decimal('rating_average', 3, 2)->default(0); // Promedio de calificaciones
            $table->integer('total_jobs')->default(0); // Total de trabajos realizados
            $table->integer('profile_completeness')->default(0); // % de completitud del perfil
            
            $table->timestamps();
            
            // Índices para optimizar búsquedas
            $table->index(['is_verified', 'is_available']);
            $table->index('specialty');
            $table->index('rating_average');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('technician_profiles');
    }
};

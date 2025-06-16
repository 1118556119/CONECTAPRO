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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            
            // Relaciones
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('technician_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('service_request_id')->constrained()->onDelete('cascade');
            
            // Calificaciones
            $table->integer('overall_rating')->unsigned()->between(1, 5); // Calificación general
            $table->integer('quality_rating')->nullable()->unsigned()->between(1, 5); // Calidad del trabajo
            $table->integer('punctuality_rating')->nullable()->unsigned()->between(1, 5); // Puntualidad
            $table->integer('communication_rating')->nullable()->unsigned()->between(1, 5); // Comunicación
            $table->integer('price_rating')->nullable()->unsigned()->between(1, 5); // Relación calidad-precio
            
            // Comentarios
            $table->text('comment')->nullable(); // Comentario principal
            $table->text('pros')->nullable(); // Aspectos positivos
            $table->text('cons')->nullable(); // Aspectos a mejorar
            
            // Estado y visibilidad
            $table->boolean('is_public')->default(true); // Visible públicamente
            $table->boolean('is_verified')->default(false); // Verificado por admin
            $table->boolean('is_recommended')->default(true); // ¿Recomendaría al técnico?
            
            // Respuesta del técnico
            $table->text('technician_response')->nullable();
            $table->timestamp('technician_response_at')->nullable();
            
            $table->timestamps();
            
            // Evitar reseñas duplicadas para el mismo servicio
            $table->unique(['client_id', 'service_request_id']);
            
            // Índices
            $table->index(['technician_id', 'is_public']);
            $table->index('overall_rating');
            $table->index('is_verified');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};

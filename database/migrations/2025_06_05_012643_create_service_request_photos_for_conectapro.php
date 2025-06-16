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
        Schema::create('service_request_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_request_id')->constrained()->onDelete('cascade');
            
            // Información del archivo
            $table->string('file_name'); // Nombre del archivo en el servidor
            $table->string('original_name'); // Nombre original del archivo
            $table->string('file_path'); // Ruta completa del archivo
            $table->string('mime_type'); // Tipo MIME del archivo
            $table->bigInteger('file_size'); // Tamaño en bytes
            
            // Clasificación de la foto
            $table->enum('photo_type', [
                'problem',       // Foto del problema inicial
                'equipment',     // Foto del equipo
                'diagnostic',    // Foto del diagnóstico
                'progress',      // Foto durante el trabajo
                'solution',      // Foto de la solución
                'before',        // Antes del servicio
                'after',         // Después del servicio
                'invoice',       // Factura o recibo
                'other'         // Otros
            ])->default('problem');
            
            // Metadatos adicionales
            $table->text('description')->nullable(); // Descripción de la foto
            $table->integer('sort_order')->default(0); // Orden de visualización
            $table->boolean('is_public')->default(true); // Visible para el cliente
            $table->json('metadata')->nullable(); // Metadatos adicionales (EXIF, etc.)
            
            // Quien subió la foto
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            
            $table->timestamps();
            
            // Índices
            $table->index(['service_request_id', 'photo_type']);
            $table->index('uploaded_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_request_photos');
    }
};

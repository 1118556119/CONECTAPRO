<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_education', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Campos principales
            $table->string('education_level'); // Nivel educativo
            $table->string('institution'); // Institución
            $table->string('degree'); // Título obtenido
            $table->year('start_year')->nullable(); // Año inicio
            $table->year('graduation_year')->nullable(); // Año graduación
            $table->string('country', 100)->default('Colombia'); // País
            $table->string('department', 100)->nullable(); // Departamento
            $table->string('city', 100)->nullable(); // Ciudad
            
            // Certificado
            $table->string('certificate_file_path')->nullable();
            $table->string('certificate_original_name')->nullable();
            $table->string('certificate_mime_type')->nullable();
            $table->unsignedBigInteger('certificate_file_size')->nullable();
            
            // Campos de control
            $table->integer('sort_order')->default(0);
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_verified')->default(false);
            
            $table->timestamps();
            
            // Índices
            $table->index(['user_id', 'is_primary']);
            $table->index(['user_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_education');
    }
};
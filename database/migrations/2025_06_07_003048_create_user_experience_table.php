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
        Schema::create('user_experience', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Campos principales
            $table->string('company_name'); // Nombre de la empresa
            $table->string('position'); // Cargo
            $table->date('start_date'); // Fecha inicio
            $table->date('end_date')->nullable(); // Fecha fin
            $table->boolean('currently_working')->default(false); // Trabajo actual
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
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_verified')->default(false);
            
            $table->timestamps();
            
            // Índices
            $table->index(['user_id', 'is_featured']);
            $table->index(['user_id', 'currently_working']);
            $table->index(['user_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_experience');
    }
};

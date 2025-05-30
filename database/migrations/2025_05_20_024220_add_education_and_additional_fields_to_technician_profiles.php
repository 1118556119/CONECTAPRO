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
        Schema::table('technician_profiles', function (Blueprint $table) {
            // Formación académica (asegurarse que sean del tipo correcto)
            $table->string('education_level')->nullable();  // Si es un select, string es correcto
            $table->string('education')->nullable();
            $table->string('institution')->nullable();
            $table->string('graduation_year')->nullable();  // Si envías como string, no usar year()
            $table->string('start_year')->nullable();       // Si envías como string, no usar year()
            $table->string('education_status')->nullable();
            $table->string('thesis_title')->nullable();
            $table->string('education_country')->nullable();
            $table->string('education_city')->nullable();
            
            // Experiencia laboral (complementos)
            $table->string('current_company')->nullable()->comment('Empresa actual');
            $table->string('position')->nullable()->comment('Posición o cargo actual');
            
            // Otros estudios
            $table->string('certifications')->nullable()->comment('Certificaciones obtenidas');
            $table->string('languages')->nullable()->comment('Idiomas que maneja');
            $table->text('additional_courses')->nullable()->comment('Cursos adicionales');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('technician_profiles', function (Blueprint $table) {
            // Eliminar columnas añadidas
            $table->dropColumn([
                'education_level', 'education', 'institution', 'graduation_year',
                'start_year', 'education_status', 'thesis_title', 'education_country',
                'education_city', 'current_company', 'position', 'certifications',
                'languages', 'additional_courses'
            ]);
        });
    }
};

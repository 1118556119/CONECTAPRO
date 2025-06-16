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
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique(); // Número único de solicitud
            
            // Relaciones
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('technician_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Información del servicio
            $table->enum('service_type', [
                'correctivo',    // Mantenimiento correctivo
                'preventivo',    // Mantenimiento preventivo
                'instalacion',   // Instalación de equipos
                'limpieza',      // Limpieza de equipos
                'reparacion',    // Reparación
                'asesoria',      // Asesoría técnica
                'diagnostico',   // Diagnóstico
                'otro'          // Otro tipo
            ]);
            
            $table->enum('urgency_level', ['baja', 'media', 'alta', 'critica'])->default('media');
            $table->text('description'); // Descripción detallada del problema
            
            // Información del equipo
            $table->enum('equipment_type', [
                'desktop',       // Computadora de escritorio
                'laptop',        // Laptop
                'server',        // Servidor
                'printer',       // Impresora
                'network',       // Equipos de red
                'mobile',        // Dispositivos móviles
                'tablet',        // Tablets
                'scanner',       // Escáneres
                'projector',     // Proyectores
                'other'         // Otros
            ]);
            $table->string('equipment_brand')->nullable(); // Marca del equipo
            $table->string('equipment_model')->nullable(); // Modelo del equipo
            $table->text('service_details')->nullable(); // Detalles adicionales del servicio
            
            // Ubicación del servicio
            $table->string('service_address');
            $table->string('service_city');
            $table->string('service_postal_code')->nullable();
            $table->text('location_notes')->nullable(); // Notas sobre la ubicación
            
            // Programación del servicio
            $table->date('preferred_date')->nullable();
            $table->enum('preferred_time', ['morning', 'afternoon', 'evening', 'flexible'])->nullable();
            $table->text('scheduling_notes')->nullable();
            
            // Estado y seguimiento
            $table->enum('status', [
                'pending',       // Pendiente de asignación
                'quoted',        // Cotizado
                'accepted',      // Cotización aceptada
                'assigned',      // Asignado a técnico
                'in_progress',   // En progreso
                'completed',     // Completado
                'cancelled',     // Cancelado
                'rejected'       // Rechazado
            ])->default('pending');
            
            // Información financiera
            $table->decimal('estimated_cost', 10, 2)->nullable();
            $table->decimal('quoted_price', 10, 2)->nullable();
            $table->decimal('final_cost', 10, 2)->nullable();
            $table->text('cost_breakdown')->nullable(); // Desglose de costos
            
            // Timestamps de seguimiento
            $table->timestamp('quoted_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            
            // Notas y observaciones
            $table->text('client_notes')->nullable();
            $table->text('technician_notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->text('cancellation_reason')->nullable();
            
            $table->timestamps();
            
            // Índices para optimizar consultas
            $table->index(['client_id', 'status']);
            $table->index(['technician_id', 'status']);
            $table->index('status');
            $table->index('urgency_level');
            $table->index('service_type');
            $table->index('preferred_date');
            $table->index('service_city');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_requests');
    }
};

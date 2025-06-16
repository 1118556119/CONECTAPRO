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
        Schema::table('service_requests', function (Blueprint $table) {
            // Agregar campos para rechazo de cotizaciones
            if (!Schema::hasColumn('service_requests', 'rejection_reason')) {
                $table->string('rejection_reason')->nullable()->after('cancellation_reason');
            }
            
            if (!Schema::hasColumn('service_requests', 'rejection_comments')) {
                $table->text('rejection_comments')->nullable()->after('rejection_reason');
            }
            
            // Agregar timestamp para rechazo
            if (!Schema::hasColumn('service_requests', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('accepted_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            // Eliminar campos agregados
            if (Schema::hasColumn('service_requests', 'rejection_reason')) {
                $table->dropColumn('rejection_reason');
            }
            
            if (Schema::hasColumn('service_requests', 'rejection_comments')) {
                $table->dropColumn('rejection_comments');
            }
            
            if (Schema::hasColumn('service_requests', 'rejected_at')) {
                $table->dropColumn('rejected_at');
            }
        });
    }
};
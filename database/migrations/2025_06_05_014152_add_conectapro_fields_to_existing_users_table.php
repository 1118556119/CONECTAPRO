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
        Schema::table('users', function (Blueprint $table) {
            // Solo agregar las columnas que realmente faltan
            
            // Cambiar 'role' por 'user_type' (más específico)
            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }
            $table->enum('user_type', ['client', 'technician'])->default('client')->after('password');
            
            // Agregar nuevas columnas
            $table->string('address')->nullable()->after('user_type');
            $table->string('city')->nullable()->after('address');
            $table->string('postal_code')->nullable()->after('city');
            $table->boolean('is_active')->default(true)->after('postal_code');
            $table->timestamp('last_login_at')->nullable()->after('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Revertir cambios
            $table->dropColumn(['user_type', 'address', 'city', 'postal_code', 'is_active', 'last_login_at']);
            $table->string('role')->default('client')->after('password');
        });
    }
};

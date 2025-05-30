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
            $table->boolean('verified')->default(false)->after('position');
            $table->boolean('available')->default(true)->after('verified');
            $table->decimal('hourly_rate', 8, 2)->nullable()->after('additional_courses');
            $table->string('service_area')->nullable()->after('hourly_rate');
            $table->json('availability_schedule')->nullable()->after('service_area');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('technician_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'verified',
                'available',
                'hourly_rate',
                'service_area',
                'availability_schedule',
            ]);
        });
    }
};

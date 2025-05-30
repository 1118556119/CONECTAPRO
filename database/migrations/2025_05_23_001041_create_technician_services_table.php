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
        Schema::create('technician_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('technician_profile_id')->constrained()->onDelete('cascade');
            $table->string('service_name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->integer('duration_minutes')->default(60);
            $table->boolean('is_onsite')->default(true);
            $table->boolean('is_remote')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('technician_services');
    }
};

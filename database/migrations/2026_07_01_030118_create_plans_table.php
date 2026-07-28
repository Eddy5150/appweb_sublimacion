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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('slug')->unique();
            $table->text('descripcion')->nullable();
            $table->decimal('precio_mensual', 10, 2)->default(0);
            $table->decimal('precio_anual', 10, 2)->default(0);
            $table->integer('max_usuarios')->default(1);
            $table->integer('max_productos')->default(50);
            $table->integer('max_clientes')->default(100);
            $table->boolean('crm_incluido')->default(false);
            $table->boolean('reportes_avanzados')->default(false);
            $table->boolean('exportacion_datos')->default(false);
            $table->boolean('api_integraciones')->default(false);
            $table->boolean('soporte_prioritario')->default(false);
            $table->json('caracteristicas')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};

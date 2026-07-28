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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained()->cascadeOnDelete();
            $table->string('estado')->default('activa');
            $table->timestamp('fecha_inicio')->nullable();
            $table->timestamp('fecha_renovacion')->nullable();
            $table->timestamp('fecha_cancelacion')->nullable();
            $table->string('ciclo_pago')->default('mensual');
            $table->decimal('precio_pagado', 10, 2)->default(0);
            $table->string('metodo_pago')->nullable();
            $table->string('referencia_pago')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_automation_rules', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->boolean('activa')->default(true);
            $table->string('condicion_tipo', 100);
            $table->string('condicion_valor');
            $table->json('condicion_extra')->nullable();
            $table->string('accion_tipo', 100);
            $table->string('accion_valor');
            $table->json('accion_extra')->nullable();
            $table->integer('prioridad')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_automation_rules');
    }
};

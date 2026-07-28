<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_ai_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->text('mensaje');
            $table->string('tipo', 20)->default('whatsapp');
            $table->string('estado', 20)->default('pending');
            $table->string('accion_recomendada', 30)->nullable();
            $table->json('contexto')->nullable();
            $table->timestamp('enviado_en')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_ai_messages');
    }
};

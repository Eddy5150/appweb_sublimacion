<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_ai_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->string('action_taken', 50);
            $table->string('result', 20)->default('pending');
            $table->decimal('revenue_impact', 12, 2)->nullable();
            $table->text('notas')->nullable();
            $table->json('contexto_agente')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_ai_feedback');
    }
};

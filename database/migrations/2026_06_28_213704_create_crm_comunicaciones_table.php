<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_comunicaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->nullable()->constrained()->nullOnDelete();
            $table->string('tipo', 50);
            $table->string('canal', 50)->default('sistema');
            $table->string('asunto');
            $table->text('mensaje');
            $table->boolean('enviado')->default(false);
            $table->timestamp('enviado_en')->nullable();
            $table->timestamps();

            $table->index('tipo');
            $table->index('enviado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_comunicaciones');
    }
};

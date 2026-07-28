<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_producto_recomendados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained()->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('score')->default(0);
            $table->string('motivo', 255)->nullable();
            $table->timestamps();

            $table->unique(['cliente_id', 'producto_id']);
            $table->index('score');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_producto_recomendados');
    }
};

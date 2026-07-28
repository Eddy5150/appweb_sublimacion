<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_event_logs', function (Blueprint $table) {
            $table->id();
            $table->string('tipo', 100);
            $table->nullableMorphs('causable');
            $table->foreignId('cliente_id')->nullable()->constrained()->nullOnDelete();
            $table->json('data')->nullable();
            $table->timestamps();

            $table->index('tipo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_event_logs');
    }
};

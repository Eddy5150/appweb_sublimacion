<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_ai_predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('ai_score')->default(0);
            $table->decimal('purchase_probability', 5, 2)->default(0);
            $table->date('estimated_next_purchase')->nullable();
            $table->string('emotional_state', 20)->default('neutro');
            $table->string('future_trend', 10)->default('estable');
            $table->decimal('ltv_estimated', 12, 2)->default(0);
            $table->string('churn_risk', 10)->default('baja');
            $table->json('features')->nullable();
            $table->timestamp('last_calculated_at')->nullable();
            $table->timestamps();

            $table->index('ai_score');
            $table->index('churn_risk');
            $table->index('emotional_state');
            $table->index('purchase_probability');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_ai_predictions');
    }
};

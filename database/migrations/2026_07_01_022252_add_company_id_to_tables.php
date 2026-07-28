<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'users' => ['company_id'],
        'productos' => ['company_id'],
        'pedidos' => ['company_id'],
        'detalle_pedidos' => ['company_id'],
        'clientes' => ['company_id'],
        'roles' => ['company_id'],
        'stock_movements' => ['company_id'],
        'crm_event_logs' => ['company_id'],
        'crm_alerts' => ['company_id'],
        'crm_automation_rules' => ['company_id'],
        'crm_comunicaciones' => ['company_id'],
        'crm_ai_predictions' => ['company_id'],
        'crm_producto_recomendados' => ['company_id'],
        'crm_ai_messages' => ['company_id'],
        'crm_ai_feedback' => ['company_id'],
        'crm_actions_queue' => ['company_id'],
    ];

    public function up(): void
    {
        foreach ($this->tables as $table => $columns) {
            Schema::table($table, function (Blueprint $table) {
                $table->foreignId('company_id')->nullable()->constrained('companies')->cascadeOnDelete()->after('id');
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table => $columns) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropForeign(['company_id']);
                $table->dropColumn('company_id');
            });
        }
    }
};

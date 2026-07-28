<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::firstOrCreate(
            ['email' => 'admin@sublimacion.com'],
            [
                'nombre' => 'Sublimación PRO',
                'plan' => 'enterprise',
                'estado' => 'activa',
                'slug' => 'sublimacion-pro',
            ]
        );

        $tables = [
            'users', 'productos', 'clientes', 'pedidos',
            'detalle_pedidos', 'stock_movements', 'roles',
            'crm_event_logs', 'crm_alerts', 'crm_automation_rules',
            'crm_comunicaciones', 'crm_ai_predictions',
            'crm_producto_recomendados', 'crm_ai_messages',
            'crm_ai_feedback', 'crm_actions_queue',
        ];

        foreach ($tables as $table) {
            DB::table($table)
                ->whereNull('company_id')
                ->update(['company_id' => $company->id]);
        }

        $this->command->info("Company '{$company->nombre}' seeded with ID {$company->id}.");
        $this->command->info('All existing records migrated to default company.');
    }
}

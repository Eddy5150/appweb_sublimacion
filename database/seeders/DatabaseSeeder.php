<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([CompanySeeder::class]);

        $company = Company::first();
        if ($company) {
            config(['app.company_id' => $company->id]);
        }

        $this->call([
            RolesSeeder::class,
            PermissionsSeeder::class,
            UsuariosSeeder::class,
            PedidoSeeder::class,
            CrmAutomationRuleSeeder::class,
            PlanSeeder::class,
        ]);
    }
}

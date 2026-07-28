<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();

        if (! $company) {
            $this->command->warn('No company found. Run CompanySeeder first.');

            return;
        }

        $roles = [
            ['nombre' => 'admin', 'descripcion' => 'Administrador del emprendimiento'],
            ['nombre' => 'cajera', 'descripcion' => 'Personal de caja y seguimiento de pedidos'],
            ['nombre' => 'operador', 'descripcion' => 'Personal de producción y operaciones'],
            ['nombre' => 'vendedor', 'descripcion' => 'Personal de ventas y atención al cliente'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['nombre' => $role['nombre'], 'company_id' => $company->id],
                ['descripcion' => $role['descripcion']],
            );
        }
    }
}

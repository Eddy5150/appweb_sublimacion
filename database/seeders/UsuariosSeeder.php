<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuariosSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();

        if (! $company) {
            $this->command->warn('No company found. Run CompanySeeder first.');

            return;
        }

        $usuarios = [
            [
                'name' => 'Eddy Henry Zambrana Sáenz',
                'email' => 'saenzhenry150@gmail.com',
                'password' => '21092002',
                'role' => 'admin',
            ],
            [
                'name' => 'Jhonny Vallejos Catorceno',
                'email' => 'correo_del_socio@gmail.com',
                'password' => '12345678',
                'role' => 'admin',
            ],
            [
                'name' => 'Cajera',
                'email' => 'cajera@gmail.com',
                'password' => 'cajera1234',
                'role' => 'cajera',
            ],
            [
                'name' => 'Vendedor Prueba',
                'email' => 'vendedor@gmail.com',
                'password' => 'vendedor1234',
                'role' => 'vendedor',
            ],
            [
                'name' => 'Operador',
                'email' => 'operador@gmail.com',
                'password' => 'operador1234',
                'role' => 'operador',
            ],
        ];

        foreach ($usuarios as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'company_id' => $company->id,
                    'name' => $data['name'],
                    'password' => Hash::make($data['password']),
                    'role' => $data['role'],
                    'email_verified_at' => now(),
                ]
            );

            $role = Role::where('nombre', $data['role'])
                ->where('company_id', $company->id)
                ->first();

            if ($role && ! $user->roles()->where('role_id', $role->id)->exists()) {
                $user->roles()->attach($role->id);
            }
        }
    }
}

<?php

namespace Database\Seeders;

use App\Enums\Permission as PermissionEnum;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        foreach (PermissionEnum::cases() as $permission) {
            Permission::query()->updateOrCreate(
                ['nombre' => $permission->value],
                ['descripcion' => $permission->name],
            );
        }

        $admin = Role::query()->where('nombre', 'admin')->firstOrFail();
        $cajera = Role::query()->where('nombre', 'cajera')->firstOrFail();

        $admin->permissions()->sync(
            Permission::query()
                ->whereIn('nombre', PermissionEnum::valuesForAdministrador())
                ->pluck('id')
                ->all(),
        );

        $cajera->permissions()->sync(
            Permission::query()
                ->whereIn('nombre', PermissionEnum::valuesForCajera())
                ->pluck('id')
                ->all(),
        );

        $operador = Role::query()->where('nombre', 'operador')->firstOrFail();

        $operador->permissions()->sync(
            Permission::query()
                ->whereIn('nombre', PermissionEnum::valuesForOperador())
                ->pluck('id')
                ->all(),
        );

        $vendedor = Role::query()->where('nombre', 'vendedor')->firstOrFail();

        $vendedor->permissions()->sync(
            Permission::query()
                ->whereIn('nombre', PermissionEnum::valuesForVendedor())
                ->pluck('id')
                ->all(),
        );
    }
}

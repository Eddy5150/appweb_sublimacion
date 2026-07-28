<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['nombre' => 'panel.ver', 'descripcion' => 'Ver panel principal', 'grupo' => 'Panel'],
            ['nombre' => 'productos.ver', 'descripcion' => 'Ver productos', 'grupo' => 'Productos'],
            ['nombre' => 'productos.gestionar', 'descripcion' => 'Crear, editar y eliminar productos', 'grupo' => 'Productos'],
            ['nombre' => 'pedidos.ver', 'descripcion' => 'Ver pedidos', 'grupo' => 'Pedidos'],
            ['nombre' => 'pedidos.gestionar', 'descripcion' => 'Crear, editar y eliminar pedidos', 'grupo' => 'Pedidos'],
            ['nombre' => 'clientes.ver', 'descripcion' => 'Ver clientes', 'grupo' => 'Clientes'],
            ['nombre' => 'clientes.gestionar', 'descripcion' => 'Crear, editar y eliminar clientes', 'grupo' => 'Clientes'],
            ['nombre' => 'reportes.ver', 'descripcion' => 'Ver reportes y exportar', 'grupo' => 'Reportes'],
            ['nombre' => 'usuarios.gestionar', 'descripcion' => 'Gestionar usuarios y roles', 'grupo' => 'Usuarios'],
            ['nombre' => 'caja.ver', 'descripcion' => 'Ver caja y movimientos', 'grupo' => 'Caja'],
            ['nombre' => 'caja.gestionar', 'descripcion' => 'Abrir y cerrar caja', 'grupo' => 'Caja'],
            ['nombre' => 'auditoria.ver', 'descripcion' => 'Ver registro de actividad', 'grupo' => 'Auditoría'],
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(
                ['nombre' => $perm['nombre']],
                $perm
            );
        }
    }
}

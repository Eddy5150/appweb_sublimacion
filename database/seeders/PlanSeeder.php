<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'nombre' => 'Free',
                'slug' => 'free',
                'descripcion' => 'Plan gratuito para emprendedores que inician',
                'precio_mensual' => 0,
                'precio_anual' => 0,
                'max_usuarios' => 1,
                'max_productos' => 20,
                'max_clientes' => 50,
                'crm_incluido' => false,
                'reportes_avanzados' => false,
                'exportacion_datos' => false,
                'api_integraciones' => false,
                'soporte_prioritario' => false,
                'caracteristicas' => ['Panel básico', 'Gestión de productos', 'Gestión de pedidos', 'Catálogo público'],
            ],
            [
                'nombre' => 'Básico',
                'slug' => 'basic',
                'descripcion' => 'Perfecto para pequeños negocios de sublimación',
                'precio_mensual' => 99,
                'precio_anual' => 999,
                'max_usuarios' => 3,
                'max_productos' => 100,
                'max_clientes' => 200,
                'crm_incluido' => true,
                'reportes_avanzados' => false,
                'exportacion_datos' => true,
                'api_integraciones' => false,
                'soporte_prioritario' => false,
                'caracteristicas' => ['Todo Free', 'CRM básico', '3 usuarios', 'Exportación de datos', 'Proveedores', 'Compras'],
            ],
            [
                'nombre' => 'Pro',
                'slug' => 'pro',
                'descripcion' => 'Para negocios en crecimiento con equipo',
                'precio_mensual' => 199,
                'precio_anual' => 1999,
                'max_usuarios' => 10,
                'max_productos' => 500,
                'max_clientes' => 1000,
                'crm_incluido' => true,
                'reportes_avanzados' => true,
                'exportacion_datos' => true,
                'api_integraciones' => true,
                'soporte_prioritario' => false,
                'caracteristicas' => ['Todo Básico', 'Reportes avanzados', 'API integraciones', '10 usuarios', 'CRM con IA'],
            ],
            [
                'nombre' => 'Premium',
                'slug' => 'premium',
                'descripcion' => 'Solución empresarial completa con soporte prioritario',
                'precio_mensual' => 399,
                'precio_anual' => 3999,
                'max_usuarios' => 50,
                'max_productos' => 9999,
                'max_clientes' => 9999,
                'crm_incluido' => true,
                'reportes_avanzados' => true,
                'exportacion_datos' => true,
                'api_integraciones' => true,
                'soporte_prioritario' => true,
                'caracteristicas' => ['Todo Pro', 'Soporte prioritario', 'Usuarios ilimitados', 'Productos ilimitados', 'CRM completo con IA'],
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(
                ['slug' => $plan['slug']],
                $plan,
            );
        }
    }
}

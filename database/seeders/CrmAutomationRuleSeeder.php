<?php

namespace Database\Seeders;

use App\Models\CrmAutomationRule;
use Illuminate\Database\Seeder;

class CrmAutomationRuleSeeder extends Seeder
{
    public function run(): void
    {
        $reglas = [
            [
                'nombre' => 'VIP automático',
                'descripcion' => 'Si un cliente gasta más de 1.000 Bs, se marca automáticamente como VIP',
                'activa' => true,
                'condicion_tipo' => 'total_gastado_gt',
                'condicion_valor' => '1000',
                'accion_tipo' => 'marcar_estado',
                'accion_valor' => 'vip',
                'prioridad' => 100,
            ],
            [
                'nombre' => 'Inactivo por falta de compras',
                'descripcion' => 'Si un cliente no compra en más de 30 días, se marca como inactivo',
                'activa' => true,
                'condicion_tipo' => 'sin_compras_dias_gt',
                'condicion_valor' => '30',
                'accion_tipo' => 'marcar_estado',
                'accion_valor' => 'inactivo',
                'prioridad' => 90,
            ],
            [
                'nombre' => 'Alerta de riesgo',
                'descripcion' => 'Si un cliente no compra en más de 60 días, genera alerta de riesgo',
                'activa' => true,
                'condicion_tipo' => 'sin_compras_dias_gt',
                'condicion_valor' => '60',
                'accion_tipo' => 'generar_alerta',
                'accion_valor' => 'riesgo',
                'accion_extra' => [
                    'titulo' => 'Cliente en riesgo de pérdida',
                    'mensaje' => 'Este cliente lleva más de 60 días sin realizar una compra.',
                ],
                'prioridad' => 80,
            ],
            [
                'nombre' => 'Bienvenida nuevo cliente',
                'descripcion' => 'Cuando un cliente hace su primera compra, se genera mensaje de bienvenida',
                'activa' => true,
                'condicion_tipo' => 'pedidos_count_eq',
                'condicion_valor' => '1',
                'accion_tipo' => 'enviar_mensaje',
                'accion_valor' => 'bienvenida',
                'prioridad' => 70,
            ],
            [
                'nombre' => 'Nuevo cliente frecuente',
                'descripcion' => 'Si un cliente alcanza 3 o más pedidos, se marca como frecuente',
                'activa' => true,
                'condicion_tipo' => 'pedidos_count_gte',
                'condicion_valor' => '3',
                'accion_tipo' => 'marcar_estado',
                'accion_valor' => 'frecuente',
                'prioridad' => 60,
            ],
            [
                'nombre' => 'Cliente en riesgo (inactivo + alerta)',
                'descripcion' => 'Clientes con más de 60 días sin comprar y al menos 2 pedidos previos se marcan en riesgo',
                'activa' => true,
                'condicion_tipo' => 'sin_compras_dias_gt',
                'condicion_valor' => '60',
                'accion_tipo' => 'marcar_estado',
                'accion_valor' => 'en_riesgo',
                'prioridad' => 50,
            ],
            [
                'nombre' => 'Agradecimiento por compra',
                'descripcion' => 'Cuando un cliente compra, se genera mensaje de agradecimiento',
                'activa' => true,
                'condicion_tipo' => 'compro_ultimos_dias',
                'condicion_valor' => '1',
                'accion_tipo' => 'enviar_mensaje',
                'accion_valor' => 'agradecimiento',
                'prioridad' => 40,
            ],
        ];

        foreach ($reglas as $regla) {
            CrmAutomationRule::create($regla);
        }
    }
}

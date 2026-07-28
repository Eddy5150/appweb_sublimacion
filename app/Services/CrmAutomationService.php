<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\CrmAiPrediction;
use App\Models\CrmAlert;
use App\Models\CrmAutomationRule;
use App\Models\CrmComunicacion;

class CrmAutomationService
{
    public function __construct(
        private CrmEventLogger $logger,
        private CrmRulesEngine $engine,
    ) {}

    public function procesarCliente(Cliente $cliente): array
    {
        $reglas = $this->engine->evaluarCliente($cliente);
        $acciones = [];

        foreach ($reglas as $regla) {
            $resultado = $this->ejecutarAccion($regla, $cliente);
            if ($resultado !== null) {
                $acciones[] = $resultado;
            }
        }

        return $acciones;
    }

    public function ejecutarAccion(CrmAutomationRule $regla, Cliente $cliente): ?array
    {
        $tipo = $regla->accion_tipo;
        $valor = $regla->accion_valor;
        $extra = $regla->accion_extra ?? [];

        return match ($tipo) {
            'marcar_estado' => $this->marcarEstado($cliente, $valor, $extra),
            'generar_alerta' => $this->generarAlerta($cliente, $valor, $extra),
            'enviar_mensaje' => $this->enviarMensaje($cliente, $valor, $extra),
            default => null,
        };
    }

    public function procesarAutomatico(): array
    {
        $resultados = [
            'reenganche' => 0,
            'warnings' => 0,
            'vip_upgrades' => 0,
            'caidas' => 0,
        ];

        $resultados['reenganche'] = $this->detectarReenganche();
        $resultados['warnings'] = $this->detectarWarning();
        $resultados['vip_upgrades'] = $this->detectarVipUpgrade();
        $resultados['caidas'] = $this->detectarCaidaFrecuencia();

        return $resultados;
    }

    public function detectarReenganche(): int
    {
        $contador = 0;

        $inactivos = Cliente::query()
            ->select('clientes.*')
            ->withCount('pedidos')
            ->withAggregate('pedidos', 'created_at', 'max')
            ->withAggregate('pedidos', 'created_at', 'min')
            ->where('estado', 'inactivo')
            ->whereHas('pedidos')
            ->get()
            ->filter(fn (Cliente $c) => ($c->dias_ultima_compra ?? 999) >= 30);

        foreach ($inactivos as $cliente) {
            $titulo = "Re-engagement: {$cliente->nombre}";

            if ($cliente->dias_ultima_compra >= 60) {
                $mensaje = "Cliente sin compras hace {$cliente->dias_ultima_compra} días. Prioridad alta para campaña de recuperación.";
            } else {
                $mensaje = "Cliente sin compras hace {$cliente->dias_ultima_compra} días. Enviar recordatorio.";
            }

            CrmAlert::create([
                'cliente_id' => $cliente->id,
                'tipo' => 'reenganche',
                'titulo' => $titulo,
                'mensaje' => $mensaje,
            ]);

            $this->logger->log('reenganche_detectado', $cliente, $cliente, [
                'dias_ultima_compra' => $cliente->dias_ultima_compra,
                'pedidos_previos' => $cliente->pedidos_count,
                'segmento_actual' => $cliente->estado,
            ]);

            $contador++;
        }

        return $contador;
    }

    public function detectarWarning(): int
    {
        $contador = 0;

        $warning = Cliente::query()
            ->select('clientes.*')
            ->withCount('pedidos')
            ->withAggregate('pedidos', 'created_at', 'max')
            ->withAggregate('pedidos', 'created_at', 'min')
            ->whereIn('estado', ['frecuente', 'vip'])
            ->get()
            ->filter(fn (Cliente $c) => ($c->dias_ultima_compra ?? 999) >= 15
                && $c->dias_ultima_compra < 30);

        foreach ($warning as $cliente) {
            CrmAlert::create([
                'cliente_id' => $cliente->id,
                'tipo' => 'warning',
                'titulo' => "Alerta temprana: {$cliente->nombre}",
                'mensaje' => "Cliente {$cliente->estado} sin comprar hace {$cliente->dias_ultima_compra} días. Solía comprar cada ~{$cliente->frecuencia_promedio} días.",
            ]);

            $this->logger->log('warning_detectado', $cliente, $cliente, [
                'dias_ultima_compra' => $cliente->dias_ultima_compra,
                'frecuencia_promedio' => $cliente->frecuencia_promedio,
                'estado' => $cliente->estado,
            ]);

            $contador++;
        }

        return $contador;
    }

    public function detectarVipUpgrade(): int
    {
        $contador = 0;

        $candidatos = CrmAiPrediction::with('cliente')
            ->whereHas('cliente', fn ($q) => $q->where('estado', '!=', 'vip'))
            ->orderByDesc('ltv_estimated')
            ->take(10)
            ->get()
            ->filter(fn (CrmAiPrediction $p) => $p->cliente
                && ($p->cliente->total_gastado ?? 0) >= 500);

        foreach ($candidatos as $pred) {
            $cliente = $pred->cliente;
            $gastado = (float) ($cliente->total_gastado ?? 0);

            if ($gastado < 500) {
                continue;
            }

            $cercaDeVip = $gastado >= 800
                ? "Muy cerca de VIP (a {$gastado} Bs de los 1000)."
                : "Ha gastado {$gastado} Bs con potencial para segmento VIP.";

            CrmAlert::create([
                'cliente_id' => $cliente->id,
                'tipo' => 'vip_upgrade',
                'titulo' => "Candidato VIP: {$cliente->nombre}",
                'mensaje' => $cercaDeVip.' Score IA: '.$pred->ai_score.'. LTV estimado: '.$pred->ltv_estimated.' Bs.',
            ]);

            $this->logger->log('vip_candidate_detected', $cliente, $cliente, [
                'total_gastado' => $gastado,
                'ai_score' => $pred->ai_score,
                'ltv_estimated' => $pred->ltv_estimated,
            ]);

            $contador++;
        }

        return $contador;
    }

    public function detectarCaidaFrecuencia(): int
    {
        $contador = 0;

        $caidas = Cliente::query()
            ->select('clientes.*')
            ->withCount('pedidos')
            ->withAggregate('pedidos', 'created_at', 'max')
            ->withAggregate('pedidos', 'created_at', 'min')
            ->whereIn('estado', ['frecuente', 'vip'])
            ->get()
            ->filter(fn (Cliente $c) => $c->pedidos_count >= 3
                && $c->tendencia === 'baja');

        foreach ($caidas as $cliente) {
            CrmAlert::create([
                'cliente_id' => $cliente->id,
                'tipo' => 'caida_frecuencia',
                'titulo' => "Riesgo de pérdida: {$cliente->nombre}",
                'mensaje' => "Cliente {$cliente->estado} con tendencia a la baja. Pedidos: {$cliente->pedidos_count}, última compra hace {$cliente->dias_ultima_compra} días.",
            ]);

            $this->logger->log('caida_frecuencia_detectada', $cliente, $cliente, [
                'pedidos_count' => $cliente->pedidos_count,
                'dias_ultima_compra' => $cliente->dias_ultima_compra,
                'tendencia' => $cliente->tendencia,
                'frecuencia_promedio' => $cliente->frecuencia_promedio,
            ]);

            $contador++;
        }

        return $contador;
    }

    private function marcarEstado(Cliente $cliente, string $nuevoEstado, array $extra): ?array
    {
        if (! in_array($nuevoEstado, ['nuevo', 'frecuente', 'vip', 'inactivo', 'en_riesgo'])) {
            return null;
        }

        $estadoAnterior = $cliente->estado;

        if ($estadoAnterior === $nuevoEstado) {
            return null;
        }

        $cliente->updateQuietly(['estado' => $nuevoEstado]);

        $this->logger->segmentoCambiado($cliente, $estadoAnterior, $nuevoEstado);

        return [
            'accion' => 'marcar_estado',
            'detalle' => "{$estadoAnterior} → {$nuevoEstado}",
        ];
    }

    private function generarAlerta(Cliente $cliente, string $tipoAlerta, array $extra): ?array
    {
        $titulo = $extra['titulo'] ?? $this->tituloAlerta($tipoAlerta, $cliente);
        $mensaje = $extra['mensaje'] ?? $this->mensajeAlerta($tipoAlerta, $cliente);

        CrmAlert::create([
            'cliente_id' => $cliente->id,
            'tipo' => $tipoAlerta,
            'titulo' => $titulo,
            'mensaje' => $mensaje,
        ]);

        $this->logger->alertaGenerada($cliente, $tipoAlerta);

        return [
            'accion' => 'generar_alerta',
            'detalle' => $titulo,
        ];
    }

    private function enviarMensaje(Cliente $cliente, string $tipoMensaje, array $extra): ?array
    {
        $asunto = $extra['asunto'] ?? $this->asuntoMensaje($tipoMensaje, $cliente);
        $mensaje = $extra['mensaje'] ?? $this->cuerpoMensaje($tipoMensaje, $cliente);
        $canal = $extra['canal'] ?? 'sistema';

        CrmComunicacion::create([
            'cliente_id' => $cliente->id,
            'tipo' => $tipoMensaje,
            'canal' => $canal,
            'asunto' => $asunto,
            'mensaje' => $mensaje,
        ]);

        return [
            'accion' => 'enviar_mensaje',
            'detalle' => $asunto,
        ];
    }

    private function tituloAlerta(string $tipo, Cliente $cliente): string
    {
        return match ($tipo) {
            'riesgo' => "{$cliente->nombre} está en riesgo de pérdida",
            'vip_compra' => "{$cliente->nombre} realizó una compra VIP",
            'perdida_frecuente' => "{$cliente->nombre} dejó de comprar",
            'nuevo_cliente' => "Nuevo cliente: {$cliente->nombre}",
            'segmento_cambiado' => "{$cliente->nombre} cambió de segmento",
            default => "Alerta: {$cliente->nombre}",
        };
    }

    private function mensajeAlerta(string $tipo, Cliente $cliente): string
    {
        $dias = $cliente->dias_ultima_compra ?? '?';

        return match ($tipo) {
            'riesgo' => "Hace {$dias} días que no compra. Tiene {$cliente->pedidos_count} pedidos previos por {$cliente->total_gastado} Bs.",
            'vip_compra' => "Ha acumulado {$cliente->total_gastado} Bs en {$cliente->pedidos_count} pedidos. Cliente VIP activo.",
            'perdida_frecuente' => "Solía comprar frecuentemente ({$cliente->pedidos_count} pedidos) pero no registra compras desde hace {$dias} días.",
            'nuevo_cliente' => "{$cliente->nombre} acaba de registrar su primera compra en el sistema.",
            'segmento_cambiado' => "El cliente pasó al segmento {$cliente->estado}.",
            default => '',
        };
    }

    private function asuntoMensaje(string $tipo, Cliente $cliente): string
    {
        return match ($tipo) {
            'bienvenida' => "¡Bienvenido, {$cliente->nombre}!",
            'agradecimiento' => "Gracias por tu compra, {$cliente->nombre}",
            'recuperacion' => "Te extrañamos, {$cliente->nombre}",
            'notificacion' => "Notificación importante, {$cliente->nombre}",
            default => "Mensaje para {$cliente->nombre}",
        };
    }

    private function cuerpoMensaje(string $tipo, Cliente $cliente): string
    {
        return match ($tipo) {
            'bienvenida' => "¡Hola {$cliente->nombre}! Gracias por confiar en nosotros. Estamos emocionados de tenerte como cliente.",
            'agradecimiento' => "Hola {$cliente->nombre}, gracias por tu compra. Esperamos verte pronto nuevamente.",
            'recuperacion' => "Hola {$cliente->nombre}, te extrañamos. Hace tiempo que no sabíamos de ti. ¡Te esperamos!",
            'notificacion' => "Hola {$cliente->nombre}, tenemos información importante para ti.",
            default => '',
        };
    }
}

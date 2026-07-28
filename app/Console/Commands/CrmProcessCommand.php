<?php

namespace App\Console\Commands;

use App\Models\Cliente;
use App\Models\CrmEventLog;
use App\Services\CrmAutomationService;
use App\Services\CrmService;
use Illuminate\Console\Command;

class CrmProcessCommand extends Command
{
    protected $signature = 'crm:process
        {--chunk=100 : Tamaño del chunk para procesar}
        {--dry-run : Ejecutar sin persistir cambios}';

    protected $description = 'Procesar CRM: clasificar clientes, ejecutar reglas, generar alertas';

    public function handle(CrmService $crm, CrmAutomationService $automation): int
    {
        $this->info('Iniciando procesamiento CRM...');
        $chunkSize = (int) $this->option('chunk');
        $dryRun = (bool) $this->option('dry-run');

        $contador = 0;
        $reglasDisparadas = 0;

        Cliente::query()
            ->withCount('pedidos')
            ->withAggregate('pedidos', 'created_at', 'max')
            ->withAggregate('pedidos', 'created_at', 'min')
            ->chunk($chunkSize, function ($clientes) use ($crm, $automation, $dryRun, &$contador, &$reglasDisparadas) {
                foreach ($clientes as $cliente) {
                    $clasificado = $crm->clasificarCliente($cliente);

                    if ($clasificado) {
                        $contador++;
                    }

                    $acciones = $automation->procesarCliente($cliente);

                    if (! $dryRun) {
                        foreach ($acciones as $accion) {
                            CrmEventLog::create([
                                'tipo' => 'regla_automatizacion',
                                'causable_type' => $cliente::class,
                                'causable_id' => $cliente->id,
                                'cliente_id' => $cliente->id,
                                'data' => $accion,
                            ]);
                        }
                    }

                    $reglasDisparadas += count($acciones);
                }
            });

        $stats = CrmEventLog::where('tipo', 'regla_automatizacion')
            ->where('created_at', '>=', now()->subMinute())
            ->count();

        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Clientes clasificados', $contador],
                ['Reglas disparadas', $reglasDisparadas],
                ['Eventos registrados (esta ejecución)', $stats],
                ['Dry run', $dryRun ? 'Sí' : 'No'],
            ]
        );

        $this->info('Procesamiento CRM completado.');

        return Command::SUCCESS;
    }
}

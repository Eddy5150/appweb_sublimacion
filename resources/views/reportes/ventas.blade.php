<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Ventas</title>
    <style>
        body { font-family: sans-serif; font-size: 10pt; color: #333; }
        h1 { font-size: 18pt; margin-bottom: 4px; }
        .meta { color: #666; font-size: 9pt; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #1a1a2e; color: white; padding: 8px 10px; text-align: left; font-size: 9pt; }
        td { padding: 6px 10px; border-bottom: 1px solid #eee; font-size: 9pt; }
        .total-row { font-weight: bold; background: #f5f5f5; }
        .total-row td { border-top: 2px solid #333; }
        .badge {
            display: inline-block; padding: 2px 8px; border-radius: 4px;
            font-size: 8pt; font-weight: bold;
        }
        .badge-entregado { background: #d4edda; color: #155724; }
        .badge-proceso { background: #cce5ff; color: #004085; }
        .badge-pendiente { background: #fff3cd; color: #856404; }
        .badge-listo { background: #d1ecf1; color: #0c5460; }
        .footer { margin-top: 30px; font-size: 8pt; color: #999; text-align: center; }
    </style>
</head>
<body>
    <h1>Reporte de Ventas</h1>
    <p class="meta">
        Período: {{ $desde }} al {{ $hasta }} |
        Generado: {{ $fecha }}
    </p>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Total</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ventas as $v)
            <tr>
                <td>{{ $v->codigo ?? $v->id }}</td>
                <td>{{ $v->created_at->format('d/m/Y') }}</td>
                <td>{{ $v->cliente_nombre }}</td>
                <td>Bs {{ number_format((float) $v->total, 2) }}</td>
                <td>
                    <span class="badge badge-{{ $v->estado }}">
                        {{ str_replace('_', ' ', $v->estado) }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="3" style="text-align: right;">Total General</td>
                <td>Bs {{ number_format($total, 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Sublimación Samuzabety — Villa Tunari, Cochabamba — {{ $fecha }}
    </div>
</body>
</html>

<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    use BelongsToCompany, HasFactory;

    protected $table = 'clientes';

    protected $fillable = [
        'company_id',
        'nombre',
        'telefono',
        'email',
        'direccion',
        'estado',
    ];

    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class);
    }

    public function getTotalGastadoAttribute(): float
    {
        if (array_key_exists('total_detalles_sum', $this->attributes)) {
            return (float) ($this->attributes['total_detalles_sum'] ?? 0);
        }

        return (float) ($this->pedidos_total_sum ?? 0);
    }

    public function getPromedioCompraAttribute(): float
    {
        $count = $this->pedidos_count ?? 0;

        if ($count === 0) {
            return 0;
        }

        return round($this->total_gastado / $count, 2);
    }

    public function getUltimaCompraAttribute(): ?string
    {
        return $this->pedidos_created_at_max;
    }

    public function getPrimeraCompraAttribute(): ?string
    {
        return $this->pedidos_created_at_min;
    }

    public function getDiasUltimaCompraAttribute(): ?int
    {
        $ultima = $this->ultima_compra;

        if ($ultima === null) {
            return null;
        }

        return (int) now()->diffInDays(Carbon::parse($ultima));
    }

    public function getFrecuenciaPromedioAttribute(): ?float
    {
        $count = $this->pedidos_count ?? 0;
        $primera = $this->primera_compra;

        if ($primera === null || $count <= 1) {
            return null;
        }

        $diasActivo = (int) now()->diffInDays(Carbon::parse($primera));

        if ($diasActivo === 0) {
            return null;
        }

        return round((float) $diasActivo / $count, 1);
    }

    public function getTendenciaAttribute(): string
    {
        $count = $this->pedidos_count ?? 0;

        if ($count < 2) {
            return 'estable';
        }

        $diasUltima = $this->dias_ultima_compra;
        $frecuencia = $this->frecuencia_promedio;

        if ($diasUltima === null || $frecuencia === null) {
            return 'estable';
        }

        if ($diasUltima < $frecuencia * 0.7) {
            return 'sube';
        }

        if ($diasUltima > $frecuencia * 1.3) {
            return 'baja';
        }

        return 'estable';
    }

    public function getInsightAttribute(): ?string
    {
        $estado = $this->estado;
        $tendencia = $this->tendencia;
        $diasUltima = $this->dias_ultima_compra;

        if ($estado === 'en_riesgo') {
            return 'Cliente en riesgo ⚠️';
        }

        if ($estado === 'inactivo') {
            return 'Cliente inactivo 😴';
        }

        if ($estado === 'vip' && $tendencia === 'sube') {
            return 'Cliente muy rentable 💎';
        }

        if ($estado === 'vip') {
            return 'Cliente VIP 🌟';
        }

        if ($tendencia === 'sube') {
            return 'Cliente en crecimiento 📈';
        }

        if ($estado === 'frecuente') {
            return 'Cliente frecuente 🔄';
        }

        if ($estado === 'nuevo' && $diasUltima !== null && $diasUltima <= 30) {
            return 'Cliente nuevo 🆕';
        }

        return null;
    }

    public function getRiesgoScoreAttribute(): int
    {
        $diasUltima = $this->dias_ultima_compra;

        if ($diasUltima === null) {
            return 0;
        }

        $recency = match (true) {
            $diasUltima <= 7 => 50,
            $diasUltima <= 14 => 40,
            $diasUltima <= 30 => 30,
            $diasUltima <= 60 => 20,
            $diasUltima <= 90 => 10,
            default => 5,
        };

        $frequency = min(($this->pedidos_count ?? 0) * 10, 30);

        $value = min((int) round($this->total_gastado / 100), 20);

        return $recency + $frequency + $value;
    }

    public function getProbabilidadRetornoAttribute(): string
    {
        $score = $this->riesgo_score;

        return match (true) {
            $score >= 70 => 'alta',
            $score >= 40 => 'media',
            default => 'baja',
        };
    }

    public function scopeSegmento(Builder $query, string $segmento): Builder
    {
        return $query->where('estado', $segmento);
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->has('pedidos', '>=', 1);
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (blank($search)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($search) {
            $q->where('nombre', 'like', "%{$search}%")
                ->orWhere('telefono', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        });
    }

    public function scopeWithMetrics(Builder $query): Builder
    {
        $detallesSub = DetallePedido::query()
            ->join('pedidos', 'pedidos.id', '=', 'detalle_pedidos.pedido_id')
            ->whereColumn('pedidos.cliente_id', 'clientes.id')
            ->selectRaw('COALESCE(SUM(detalle_pedidos.precio_unitario * detalle_pedidos.cantidad), 0)');

        return $query
            ->select('clientes.*')
            ->selectSub($detallesSub, 'total_detalles_sum')
            ->withCount('pedidos')
            ->withAggregate('pedidos', 'created_at', 'max')
            ->withAggregate('pedidos', 'created_at', 'min');
    }

    public function resolveRouteBinding($value, $field = null): ?Model
    {
        return $this->forCurrentCompany()->where($field ?? $this->getRouteKeyName(), $value)->firstOrFail();
    }
}

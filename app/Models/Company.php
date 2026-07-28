<?php

namespace App\Models;

use App\Services\PlanLimitService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Company extends Model
{
    protected $fillable = [
        'nombre',
        'email',
        'plan',
        'estado',
        'slug',
        'logo',
        'direccion',
        'telefono',
        'sitio_web',
        'pais',
        'ciudad',
        'moneda',
        'zona_horaria',
        'configuracion',
    ];

    protected function casts(): array
    {
        return [
            'plan' => 'string',
            'estado' => 'string',
            'configuracion' => 'array',
        ];
    }

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class);
    }

    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class);
    }

    public function clientes(): HasMany
    {
        return $this->hasMany(Cliente::class);
    }

    public function roles(): HasMany
    {
        return $this->hasMany(Role::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    public function proveedores(): HasMany
    {
        return $this->hasMany(Proveedor::class);
    }

    public function compras(): HasMany
    {
        return $this->hasMany(Compra::class);
    }

    public function planLimits(): array
    {
        return app(PlanLimitService::class)->checkCompanyLimits($this);
    }

    public static function setTenantCache(self $company): void
    {
        cache()->set("tenant.company.{$company->id}.data", $company, 3600);
    }

    public function scopeWhereActive($query)
    {
        return $query->where('estado', 'activa');
    }

    public function isActive(): bool
    {
        return $this->estado === 'activa';
    }

    public function isSuspended(): bool
    {
        return $this->estado === 'suspendida';
    }
}

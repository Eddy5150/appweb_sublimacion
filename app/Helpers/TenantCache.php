<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;

class TenantCache
{
    public static function key(string $key): string
    {
        $companyId = auth()->user()?->company_id ?? config('app.company_id', 'global');

        return "tenant.{$companyId}.{$key}";
    }

    public static function remember(string $key, \DateTimeInterface|\DateInterval|int $ttl, \Closure $callback, ?string $store = null): mixed
    {
        return Cache::store($store)->remember(self::key($key), $ttl, $callback);
    }

    public static function forget(string $key, ?string $store = null): bool
    {
        return Cache::store($store)->forget(self::key($key));
    }

    public static function flush(?string $store = null): bool
    {
        $companyId = auth()->user()?->company_id ?? config('app.company_id', 'global');

        return Cache::store($store)->tags(["tenant.{$companyId}"])->flush();
    }
}

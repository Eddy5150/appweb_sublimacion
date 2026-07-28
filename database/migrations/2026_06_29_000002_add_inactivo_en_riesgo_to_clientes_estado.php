<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE clientes MODIFY COLUMN estado ENUM('nuevo','frecuente','vip','inactivo','en_riesgo') NOT NULL DEFAULT 'nuevo'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE clientes MODIFY COLUMN estado ENUM('nuevo','frecuente','vip') NOT NULL DEFAULT 'nuevo'");
        }
    }
};

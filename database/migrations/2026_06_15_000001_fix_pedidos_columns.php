<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            if (! Schema::hasColumn('pedidos', 'cliente_direccion')) {
                $table->string('cliente_direccion')->nullable()->after('cliente_telefono');
            }
        });

        if (Schema::hasColumn('pedidos', 'detalle') && ! Schema::hasColumn('pedidos', 'detalles_diseno')) {
            Schema::table('pedidos', function (Blueprint $table) {
                $table->renameColumn('detalle', 'detalles_diseno');
            });
        }
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            if (Schema::hasColumn('pedidos', 'cliente_direccion')) {
                $table->dropColumn('cliente_direccion');
            }
        });

        if (Schema::hasColumn('pedidos', 'detalles_diseno') && ! Schema::hasColumn('pedidos', 'detalle')) {
            Schema::table('pedidos', function (Blueprint $table) {
                $table->renameColumn('detalles_diseno', 'detalle');
            });
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->string('codigo')->nullable()->unique()->after('id');
            $table->timestamp('cancelado_en')->nullable()->after('fecha_entrega');
            $table->string('cancelado_por')->nullable()->after('cancelado_en');
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropColumn(['codigo', 'cancelado_en', 'cancelado_por']);
        });
    }
};

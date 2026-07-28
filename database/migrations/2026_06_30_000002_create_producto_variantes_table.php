<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('producto_variantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->string('tipo');        // talla, color, modelo
            $table->string('valor');       // S, M, L | Rojo, Azul | Basico, Premium
            $table->decimal('precio_ajuste', 10, 2)->default(0);
            $table->integer('stock')->default(0);
            $table->string('sku')->nullable();
            $table->string('imagen')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['producto_id', 'tipo', 'valor']);
        });

        Schema::create('producto_imagenes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->string('url');
            $table->string('alt')->nullable();
            $table->integer('orden')->default(0);
            $table->timestamps();
        });

        Schema::create('caja_aperturas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('monto_inicial', 12, 2)->default(0);
            $table->decimal('monto_final', 12, 2)->nullable();
            $table->timestamp('apertura_en')->useCurrent();
            $table->timestamp('cierre_en')->nullable();
            $table->string('estado')->default('abierta'); // abierta, cerrada
            $table->text('notas_apertura')->nullable();
            $table->text('notas_cierre')->nullable();
            $table->timestamps();
        });

        Schema::create('caja_movimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('caja_apertura_id')->constrained('caja_aperturas')->cascadeOnDelete();
            $table->foreignId('pedido_id')->nullable()->constrained()->nullOnDelete();
            $table->string('tipo'); // ingreso, egreso, ajuste
            $table->string('concepto');
            $table->decimal('monto', 12, 2);
            $table->string('metodo_pago')->nullable(); // efectivo, tarjeta, transferencia, qr
            $table->string('referencia')->nullable();
            $table->foreignId('user_id')->constrained();
            $table->text('notas')->nullable();
            $table->timestamps();
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('tipo'); // create, update, delete, login, export
            $table->string('modulo'); // productos, pedidos, clientes, caja, etc
            $table->string('accion');
            $table->nullableMorphs('registro');
            $table->json('detalles')->nullable();
            $table->string('ip')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('caja_movimientos');
        Schema::dropIfExists('caja_aperturas');
        Schema::dropIfExists('producto_imagenes');
        Schema::dropIfExists('producto_variantes');
    }
};

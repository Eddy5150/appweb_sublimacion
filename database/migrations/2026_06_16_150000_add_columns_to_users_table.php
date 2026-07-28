<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'telefono')) {
                $table->string('telefono', 20)->nullable()->after('email');
            }

            if (! Schema::hasColumn('users', 'estado')) {
                $table->string('estado', 20)->default('activo')->after('password');
            }

            if (! Schema::hasColumn('users', 'fecha_ingreso')) {
                $table->date('fecha_ingreso')->nullable()->after('estado');
            }

            if (! Schema::hasColumn('users', 'fecha_fin')) {
                $table->date('fecha_fin')->nullable()->after('fecha_ingreso');
            }

            if (! Schema::hasColumn('users', 'observacion')) {
                $table->text('observacion')->nullable()->after('fecha_fin');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'telefono',
                'estado',
                'fecha_ingreso',
                'fecha_fin',
                'observacion',
            ]);
        });
    }
};

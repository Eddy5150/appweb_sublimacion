<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('logo')->nullable()->after('slug');
            $table->string('direccion', 500)->nullable()->after('email');
            $table->string('telefono', 20)->nullable()->after('direccion');
            $table->string('sitio_web')->nullable()->after('telefono');
            $table->string('pais')->nullable()->after('sitio_web');
            $table->string('ciudad')->nullable()->after('pais');
            $table->string('moneda', 10)->default('MXN')->after('ciudad');
            $table->string('zona_horaria')->nullable()->after('moneda');
            $table->json('configuracion')->nullable()->after('zona_horaria');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'logo', 'direccion', 'telefono', 'sitio_web',
                'pais', 'ciudad', 'moneda', 'zona_horaria', 'configuracion',
            ]);
        });
    }
};

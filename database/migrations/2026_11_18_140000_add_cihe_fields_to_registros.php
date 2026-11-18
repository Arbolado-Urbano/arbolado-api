<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Datos adicionales relevados por los censistas de CIHE.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('registros', function (Blueprint $table) {
            $table->string('lugar')->nullable()->after('diametro_copa')->comment('plantera, cinta verde, plaza-parque');
            $table->decimal('altura_copa', 6, 2)->nullable()->after('lugar');
            $table->decimal('altura_tronco', 6, 2)->nullable()->after('altura_copa');
            $table->string('cobertura')->nullable()->after('altura_tronco')->comment('Porcentaje de cobertura: 1-25, 26-50, 51-75, 76-100');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registros', function (Blueprint $table) {
            $table->dropColumn(['lugar', 'altura_copa', 'altura_tronco', 'cobertura']);
        });
    }
};

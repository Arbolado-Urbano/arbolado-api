<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('fuentes', function (Blueprint $table) {
            $table->string('localidad')->nullable()->after('lng')->comment('Localidad asignada a los árboles cargados por censistas de esta fuente');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fuentes', function (Blueprint $table) {
            $table->dropColumn('localidad');
        });
    }
};

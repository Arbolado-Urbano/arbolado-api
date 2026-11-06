<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('aportes_notificaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('aporte_id')->unique();
            $table->unsignedBigInteger('fuente_id')->index();
            $table->unsignedBigInteger('arbol_id')->nullable();
            $table->string('estado', 20);
            $table->timestamp('notificado_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aportes_notificaciones');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Registros extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('registros', function (Blueprint $table) {
        $table->increments('id');
        $table->unsignedInteger('arbol_id');
        $table->float('lat', 12, 10)->nullable();
        $table->float('lng', 12, 10)->nullable();
        $table->string('localidad')->default('CABA');
        $table->string('calle')->nullable();
        $table->integer('calle_altura')->nullable();
        $table->string('espacio_verde')->nullable();
        $table->decimal('altura', 6, 2)->nullable();
        $table->decimal('diametro_a_p', 6, 2)->nullable();
        $table->decimal('diametro_copa', 6, 2)->nullable();
        $table->integer('inclinacion')->nullable();
        $table->string('estado_fitosanitario')->nullable();
        $table->string('etapa_desarrollo')->nullable();
        $table->timestamp('fecha_creacion')->useCurrent();
        $table->string('removido')->nullable();
        $table->string('streetview')->nullable();
        $table->unsignedInteger('fuente_id');
        $table->unsignedInteger('especie_id');
        $table->timestamps();
      });

      Schema::table('registros', function ($table) {
        $table->foreign('fuente_id')->references('id')->on('fuentes')->onDelete('cascade');
        $table->foreign('especie_id')->references('id')->on('especies')->onDelete('cascade');
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::dropIfExists('registros');
    }
}

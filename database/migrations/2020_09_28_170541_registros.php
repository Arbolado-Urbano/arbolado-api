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
        $table->decimal('altura', 6, 2)->nullable();
        $table->decimal('diametro_a_p', 6, 2)->nullable();
        $table->decimal('diametro_copa', 6, 2)->nullable();
        $table->integer('inclinacion')->nullable();
        $table->string('estado_fitosanitario')->nullable();
        $table->string('etapa_desarrollo')->nullable();
        $table->text('notas')->nullable();
        $table->timestamp('fecha_creacion')->useCurrent();
        $table->unsignedInteger('fuente_id');
        $table->unsignedInteger('arbol_id');
        $table->unsignedInteger('usuario_id')->nullable();
        $table->timestamps();
        });
        
        Schema::table('registros', function ($table) {
        $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
        $table->foreign('arbol_id')->references('id')->on('arboles')->onDelete('cascade');
        $table->foreign('fuente_id')->references('id')->on('fuentes')->onDelete('cascade');
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

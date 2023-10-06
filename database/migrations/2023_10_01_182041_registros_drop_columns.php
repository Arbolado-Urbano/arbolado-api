<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class RegistrosDropColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('registros', function ($table) {
        $table->dropColumn('lat');
        $table->dropColumn('lng');
        $table->dropColumn('localidad');
        $table->dropColumn('calle');
        $table->dropColumn('calle_altura');
        $table->dropColumn('espacio_verde');
        $table->dropColumn('removido');
        $table->dropColumn('streetview');
        $table->dropForeign(['especie_id']);
        $table->dropColumn('especie_id');
        $table->foreign('arbol_id')->references('id')->on('arboles')->onDelete('cascade');
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table('registros', function ($table) {
        $table->float('lat', 12, 10)->nullable();
        $table->float('lng', 12, 10)->nullable();
        $table->string('localidad')->default('CABA');
        $table->string('calle')->nullable();
        $table->integer('calle_altura')->nullable();
        $table->string('espacio_verde')->nullable();
        $table->string('removido')->nullable();
        $table->string('streetview')->nullable();
        $table->unsignedInteger('especie_id');
        $table->dropForeign(['arbol_id']);
        $table->foreign('especie_id')->references('id')->on('especies')->onDelete('cascade');
      });
    }
}

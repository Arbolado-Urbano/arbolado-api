<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Especies extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('especies', function (Blueprint $table) {
        $table->increments('id');
        $table->string('nombre_cientifico')->unique();
        $table->string('nombre_comun')->nullable();
        $table->string('origen')->nullable();
        $table->boolean('region_pampeana')->default(false);
        $table->boolean('region_nea')->default(false);
        $table->boolean('region_noa')->default(false);
        $table->boolean('region_cuyana')->default(false);
        $table->boolean('region_patagonica')->default(false);
        $table->string('procedencia_exotica')->nullable();
        $table->string('icono')->nullable();
        $table->string('img_completo')->nullable();
        $table->string('img_hoja')->nullable();
        $table->string('img_flor')->nullable();
        $table->string('img_fruto')->nullable();
        $table->string('img_fenologia')->nullable();
        $table->text('descripcion')->nullable();
        $table->string('medicinal')->default(0)->nullable();
        $table->string('comestible')->default(0)->nullable();
        $table->text('fauna_asociada')->nullable();
        $table->string('magnitud')->nullable();
        $table->string('forma')->nullable();
        $table->integer('crecimiento')->nullable();
        $table->string('follaje')->nullable();
        $table->string('flor')->nullable();
        $table->text('fruto')->nullable();
        $table->integer('asoleamiento')->nullable();
        $table->string('clima')->nullable();
        $table->string('suelo')->nullable();
        $table->integer('riego')->nullable();
        $table->text('biblio_img_fuentes')->nullable();
        $table->string('url')->nullable();
        $table->unsignedInteger('familia_id');
        $table->unsignedInteger('tipo_id');
        $table->timestamps();
      });

      Schema::table('especies', function ($table) {
        $table->foreign('familia_id')->references('id')->on('familias')->onDelete('cascade');
        $table->foreign('tipo_id')->references('id')->on('tipos')->onDelete('cascade');
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::dropIfExists('especies');
    }
}

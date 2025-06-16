<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Aportes extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('aportes', function (Blueprint $table) {
      $table->increments('id');
      $table->float('lat', 12, 10);
      $table->float('lng', 12, 10);
      $table->string('especie')->nullable();
      $table->decimal('altura', 6, 2)->nullable();
      $table->decimal('diametro_a_p', 6, 2)->nullable();
      $table->decimal('diametro_copa', 6, 2)->nullable();
      $table->integer('inclinacion')->nullable();
      $table->string('estado_fitosanitario')->nullable();
      $table->string('etapa_desarrollo')->nullable();
      $table->unsignedInteger('fuente_id');
      $table->unsignedInteger('especie_id')->nullable();
      $table->tinyInteger('cargado')->nullable();
      $table->timestamps();
    });

    Schema::table('aportes', function (Blueprint $table) {
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
    Schema::dropIfExists('aportes');
  }
}

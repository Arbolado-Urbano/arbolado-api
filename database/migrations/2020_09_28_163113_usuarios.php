<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Usuarios extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('usuarios', function (Blueprint $table) {
      $table->increments('id');
      $table->string('codigo')->unique();
      $table->string('nombre')->nullable();
      $table->unsignedInteger('fuente_id');
      $table->timestamps();
    });

    Schema::table('usuarios', function ($table) {
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
    Schema::dropIfExists('usuarios');
  }
}

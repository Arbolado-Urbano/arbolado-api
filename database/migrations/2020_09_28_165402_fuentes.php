<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Fuentes extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('fuentes', function (Blueprint $table) {
      $table->increments('id');
      $table->string('nombre');
      $table->string('slug')->nullable()->unique();
      $table->text('descripcion')->nullable();
      $table->string('email')->nullable();
      $table->string('facebook')->nullable();
      $table->string('twitter')->nullable();
      $table->string('instagram')->nullable();
      $table->string('url')->nullable();
      $table->tinyInteger('censo_org')->nullable()->comment("1: carga de censo realizado por profesional u organización null: dato aportado por usuario/a");
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('fuentes');
  }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Arboles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('arboles', function (Blueprint $table) {
        $table->increments('id');
        $table->float('lat', 12, 10)->nullable();
        $table->float('lng', 12, 10)->nullable();
        $table->string('localidad')->default('CABA');
        $table->string('calle')->nullable();
        $table->integer('calle_altura')->nullable();
        $table->string('espacio_verde')->nullable();
        $table->string('removido')->nullable();
        $table->string('streetview')->nullable();
        $table->unsignedInteger('especie_id');
        $table->timestamps();
      });

      Schema::table('arboles', function ($table) {
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
      Schema::dropIfExists('arboles');
    }
}

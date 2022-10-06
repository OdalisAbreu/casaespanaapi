<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartnersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->integer('codigoEntidad');
            $table->string('primerApellido');
            $table->string('primerNombre');
            $table->string('segundoApellido');
            $table->string('segundoNombre');
            $table->string('usuario');
            $table->string('codigoFamilia');
            $table->string('codigoMiembroFamilia');
            $table->string('codigoSocio');
            $table->string('email');
            $table->string('miembroFamilia');
            $table->string('accesoBalancePendiente');
            $table->string('accesoEstadoCuenta');
            $table->string('accesoPagoTarjetaCredito');
            $table->string('accesoPreInvitacion');
            $table->string('accesoTarjetaCredito');
            $table->string('accesoTransacciones');
            $table->string('celular');
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
        Schema::dropIfExists('partners');
    }
}

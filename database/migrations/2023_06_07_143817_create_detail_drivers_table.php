<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDetailDriversTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('detail_drivers', function (Blueprint $table) {
            $table->uuid('id_detail')->primary();
            $table->uuid('user_id');
            $table->string('nik')->unique();
            $table->string('tempat_lahir');
            $table->date('ttl');
            $table->enum('jk', ['lk', 'pr']);
            $table->string('alamat');
            $table->string('foto')->nullable();
            $table->string('kendaraan');
            $table->string('plat_no');
            $table->string('thn_kendaraan');
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->enum('status_akun', ['proses', 'tolak', 'aktif'])->default('proses');
            $table->enum('status_driver', ['motor', 'mobil']);
            $table->enum('status', ['on', 'off', 'busy'])->default('off');
            $table->timestamps();
            $table->foreign('user_id')
                ->references('id_user')
                ->on('users')
                ->onDelete('cascade');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('detail_drivers');
    }
}

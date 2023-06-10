<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDetailRestosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('detail_restos', function (Blueprint $table) {
            $table->uuid('id_detail')->primary();
            $table->uuid('user_id');
            $table->string('nik')->unique();
            $table->string('tlp')->unique();
            $table->string('tempat_lahir');
            $table->date('ttl');
            $table->string('alamat');
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->string('nama_resto');
            $table->string('jam_buka')->default('10:00')->nullable();
            $table->string('jam_tutup')->default('22:00')->nullable();
            $table->string('foto')->nullable();
            $table->enum('status_akun', ['proses', 'tolak', 'aktif'])->default('proses');
            $table->enum('status_toko', ['buka', 'tutup'])->default('tutup');
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
        Schema::dropIfExists('detail_restos');
    }
}

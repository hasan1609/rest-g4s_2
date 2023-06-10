<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProduksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('produks', function (Blueprint $table) {
            $table->uuid('id_produk')->primary();
            $table->uuid('user_id');
            $table->enum('kategori', ['makanan', 'minuman', 'lainnya']);
            $table->string('nama_produk');
            $table->string('harga');
            $table->string('foto_produk')->nullable();
            $table->text('keterangan')->nullable();
            $table->integer('terjual')->nullable();
            $table->enum('status', ['tersedia', 'habis'])->default('habis');
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
        Schema::dropIfExists('produks');
    }
}

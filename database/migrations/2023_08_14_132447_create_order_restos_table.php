<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderRestosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_restos', function (Blueprint $table) {
            $table->uuid('id_order_resto')->primary();
            $table->uuid('user_id');
            $table->uuid('produk_id');
            $table->uuid('toko_id');
            $table->string("jumlah");
            $table->string("total");
            $table->text("catatan")->nullable();
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id_user')
                ->on('users')
                ->onDelete('cascade');
            $table->foreign('toko_id')
                ->references('id_user')
                ->on('users')
                ->onDelete('cascade');
            $table->foreign('produk_id')
                ->references('id_produk')
                ->on('produks')
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
        Schema::dropIfExists('order_restos');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->string('id_booking')->unique()->primary();
            $table->uuid('customer_id');
            $table->uuid('driver_id')->nullable();
            $table->uuid('resto_id')->nullable();
            $table->enum('kategori', ['motor', 'mobil', 'resto']);
            $table->text('produk_order')->nullable();
            $table->text('ongkos_kirim');
            $table->text('biaya_pesanan')->nullable();
            $table->string('total');
            $table->text('alamat_dari');
            $table->string('latitude_dari');
            $table->string('longitude_dari');
            $table->text('alamat_tujuan');
            $table->string('latitude_tujuan');
            $table->string('longitude_tujuan');
            $table->timestamps();
            $table->foreign('customer_id')
                ->references('id_user')
                ->on('users')
                ->onDelete('cascade');
            $table->foreign('driver_id')
                ->references('id_user')
                ->on('users')
                ->onDelete('cascade');
            $table->foreign('resto_id')
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
        Schema::dropIfExists('bookings');
    }
}

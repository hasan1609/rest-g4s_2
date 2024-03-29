<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReviewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->uuid('id_review')->primary();
            $table->uuid('customer_id');
            $table->uuid('resto_id')->nullable();
            $table->uuid('driver_id')->nullable();
            $table->double('rating_driver', 2, 1)->nullable();
            $table->double('rating_resto', 2, 1)->nullable();
            $table->text('ulasan_driver')->nullable();
            $table->text('ulasan_resto')->nullable();
            $table->timestamps();

            $table->foreign('resto_id')
                ->references('user_id')
                ->on('detail_restos')
                ->onDelete('cascade');
            $table->foreign('customer_id')
                ->references('user_id')
                ->on('detail_customers')
                ->onDelete('cascade');
            $table->foreign('driver_id')
                ->references('user_id')
                ->on('detail_drivers')
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
        Schema::dropIfExists('reviews');
    }
}

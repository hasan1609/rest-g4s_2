<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->uuid('id_notification_log')->primary();
            $table->uuid('sender_id')->nullable();
            $table->uuid('recive_id')->nullable();
            $table->string('judul');
            $table->string('body');
            $table->string('data')->nullable();
            $table->enum('status', ['0','1'])->default('0');
            $table->timestamps();

            $table->foreign('sender_id')
                ->references('id_user')
                ->on('users')
                ->onDelete('cascade');
            $table->foreign('recive_id')
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
        Schema::dropIfExists('notification_logs');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransaksisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaksis', function (Blueprint $table) {
            $table->id();
            $table->string('kode_transaksi')->nullable();
            $table->integer('total_transaksi');
            $table->integer('bayar_transaksi');
            $table->integer('kembali_transaksi');
            $table->integer('total_item_transaksi');
            $table->integer('total_qty_transaksi');
            $table->string('status_transaksi')->default('belum selesai');
            $table->string('jenis_transaksi')->default('tunai');
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
        Schema::dropIfExists('transaksis');
    }
}

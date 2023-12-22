<?php

namespace App\Http\Controllers;

use App\Transaksi;
use Illuminate\Http\Request;

class TransaksiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $transaksi = Transaksi::all();
        return view('transaksi.index', ['transaksis' => $transaksi]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
        $data = Transaksi::find($id);
        return response()->json($data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        $transaksi = Transaksi::find($id);
        $transaksi->total_transaksi = $request->tbTotalTrans;
        $transaksi->bayar_transaksi = $request->tbBayarTrans;
        $transaksi->kembali_transaksi = $request->tbKembaliTrans;
        $transaksi->total_item_transaksi = $request->tbItemTrans;
        $transaksi->total_qty_transaksi = $request->tbQtyTrans;
        $transaksi->status_transaksi = $request->cbStatus;
        $transaksi->save();
        return redirect()->route('transaksi.index')->with('message', 'data berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        $transaksi = Transaksi::find($id);
        $transaksi->delete();
        return redirect()->route('transaksi.index')->with('message', 'data berhasil dihapus');
    }
    public function belumSelesai()
    {
        $transaksi = Transaksi::where('status_transaksi', 'belum selesai')->get();
        return view('transaksi.transaksiBelumDibayar', ['transaksis' => $transaksi]);
    }
}

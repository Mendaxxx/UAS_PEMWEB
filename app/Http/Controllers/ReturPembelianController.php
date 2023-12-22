<?php

namespace App\Http\Controllers;

use App\Barang;
use App\ReturPembelian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReturPembelianController extends Controller
{
    public function index()
    {
        $dataRetur = DB::table('retur_pembelians')
            ->join('barangs', 'retur_pembelians.barang_id', '=', 'barangs.id')
            ->select('barangs.*', 'retur_pembelians.*', 'barangs.id AS bid', 'retur_pembelians.id AS rpid')
            ->get();
        $barang = Barang::all();
        return view('retur.index', ['dataRetur' => $dataRetur, 'barang' => $barang]);
    }

    public function store(Request $request)
    {
        $barang = Barang::find($request->cb_barang_retur);
        # code...
        $retur = new ReturPembelian;
        $retur->no_faktur = 0;
        $retur->barang_id = $request->cb_barang_retur;
        $retur->jumlah_retur = $request->tb_jumlah_retur;
        $retur->total_harga_retur = ($request->tb_jumlah_retur * $barang->harga_barang);
        $retur->tanggal_retur = $request->tb_tanggal_retur;
        $retur->save();
        return redirect()->back()->with('message', 'retur berhasil ditambah');
    }

    public function edit($id)
    {
        # code...
        $retur = DB::table('retur_pembelians')
            ->join('barangs', 'retur_pembelians.barang_id', '=', 'barangs.id')
            ->select('barangs.*', 'retur_pembelians.*', 'barangs.id AS bid', 'retur_pembelians.id AS rpid')
            ->where('retur_pembelians.id', '=', $id)
            ->first();
        return response()->json($retur);
    }

    public function update(Request $request, $id)
    {
        $barang = Barang::find($request->cbBarangRetur);
        $retur = ReturPembelian::find($id);
        $retur->barang_id = $request->cbBarangRetur;
        $retur->jumlah_retur = $request->tbJumlahRetur;
        $retur->total_harga_retur = ($request->tbJumlahRetur * $barang->harga_barang);
        $retur->tanggal_retur = $request->tbTanggalRetur;
        $retur->save();
        return redirect()->back()->with('message', 'retur berhasil diperbaharui');
    }

    public function delete($id)
    {
        $retur = ReturPembelian::find($id);
        $retur->delete();
        return redirect()->back()->with('message', 'retur berhasil dihapus');
    }
}

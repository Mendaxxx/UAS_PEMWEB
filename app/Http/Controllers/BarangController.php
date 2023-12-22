<?php

namespace App\Http\Controllers;

use App\Barang;
use App\LogBarang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BarangController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $barang = Barang::all();
        return view('barang.index', ['barang' => $barang]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $barang = new Barang;
        $barang->nama_barang = $request->tb_nama_barang;
        $barang->desc_barang = $request->tb_desc_barang;
        $barang->satuan_barang = $request->cb_satuan;
        $barang->harga_barang = $request->tb_harga_barang;
        $barang->qty_barang = $request->tb_qty_barang;
        $barang->save();

        $logBarang = new LogBarang;
        $logBarang->barang_id = $barang->id;
        $logBarang->log_barang_status = 'masuk';
        $logBarang->log_barang_jumlah = $request->tb_qty_barang;
        $logBarang->save();

        return redirect()->route('barang.index')->with('message', 'data berhasil diinput');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
        $data = Barang::find($id);
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
        $barang = Barang::find($id);
        $barang->nama_barang = $request->tbNamaBarang;
        $barang->desc_barang = $request->tbDescBarang;
        $barang->satuan_barang = $request->cbSatuan;
        $barang->harga_barang = $request->tbHargaBarang;
        $barang->qty_barang = $request->tbQtyBarang;
        $barang->save();
        return redirect()->route('barang.index')->with('message', 'data berhasil diperbarui');
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
        $barang = Barang::find($id);
        $barang->delete();
        return redirect()->route('barang.index')->with('message', 'data berhasil dihapus');
    }

    public function tambahBarang(Request $request, $id)
    {
        $barang = Barang::find($id);
        $barang->qty_barang += $request->tbQtyBarang2;
        $barang->save();

        $logBarang = new LogBarang;
        $logBarang->barang_id = $id;
        $logBarang->log_barang_status = 'masuk';
        $logBarang->log_barang_jumlah = $request->tbQtyBarang2;
        $logBarang->save();

        return redirect()->route('barang.index')->with('message', 'stok barang berhasil ditambahkan');
    }

    public function detail($id)
    {
        $barang = Barang::find($id);
        $logBarang = DB::table('log_barangs')
            ->join('barangs', 'log_barangs.barang_id', '=', 'barangs.id')
            ->select('barangs.*', 'log_barangs.*', 'barangs.id AS bid', 'log_barangs.id AS lid')
            ->where('barangs.id', '=', $id)
            ->get();
        return view('barang.detail', ['barang' => $barang, 'logBarang' => $logBarang]);
    }
}

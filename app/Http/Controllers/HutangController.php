<?php

namespace App\Http\Controllers;

use App\Hutang;
use App\LogHutang;
use App\Pelanggan;
use App\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HutangController extends Controller
{
    //
    public function index()
    {
        $transaksi = Transaksi::where('jenis_transaksi', 'hutang')->get();
        $pelanggan = Pelanggan::all();
        $hutang = DB::table('hutangs')
            ->join('transaksis', 'hutangs.transaksi_id', '=', 'transaksis.id')
            ->join('pelanggans', 'hutangs.pelanggan_id', '=', 'pelanggans.id')
            ->select('pelanggans.*', 'transaksis.*', 'hutangs.*', 'pelanggans.id AS pid', 'transaksis.id AS tid', 'hutangs.id AS hid')
            ->get();
        return view('hutang.index', ['Datahutang' => $hutang, 'dataTransaksi' => $transaksi, 'Datapelanggan' => $pelanggan]);
    }

    public function store(Request $request)
    {
        # code...
        $transaksi = Transaksi::find($request->cb_kode_transaksi);

        $hutang = new Hutang;
        $hutang->transaksi_id = $request->cb_kode_transaksi;
        $hutang->pelanggan_id = $request->cb_pelanggan;
        $hutang->hutang_dibayar = 0;
        $hutang->hutang_total = $transaksi->total_transaksi;
        $hutang->hutang_status = 'belum lunas';
        $hutang->hutang_tenggat = $request->tb_tenggat_hutang;
        $hutang->save();
        return redirect()->back()->with('message', 'hutang berhasil dibuat');
    }

    public function edit($id)
    {
        # code...
        $hutang = DB::table('hutangs')
            ->join('transaksis', 'hutangs.transaksi_id', '=', 'transaksis.id')
            ->join('pelanggans', 'hutangs.pelanggan_id', '=', 'pelanggans.id')
            ->select('pelanggans.*', 'transaksis.*', 'hutangs.*', 'pelanggans.id AS pid', 'transaksis.id AS tid', 'hutangs.id AS hid')
            ->where('hutangs.id', '=', $id)
            ->first();
        return response()->json($hutang);
    }

    public function update(Request $request, $id)
    {
        # code...
        $transaksi = Transaksi::find($request->cbKodeTransaksi);

        $hutang = Hutang::find($id);
        $hutang->transaksi_id = $request->cbKodeTransaksi;
        $hutang->pelanggan_id = $request->cbPelanggan;
        $hutang->hutang_total = $transaksi->total_transaksi;
        $hutang->hutang_status = 'belum lunas';
        $hutang->hutang_tenggat = $request->tbTenggatHutang;
        $hutang->save();
        return redirect()->back()->with('message', 'hutang berhasil diperbaharui');
    }

    public function detail($id)
    {
        $hutang = Hutang::find($id);
        $logHutang = DB::table('log_hutangs')
            ->join('hutangs', 'log_hutangs.hutang_id', '=', 'hutangs.id')
            ->select('hutangs.*', 'log_hutangs.*', 'hutangs.id AS hid', 'log_hutangs.id AS lid')
            ->where('hutangs.id', '=', $id)
            ->get();
        return view('hutang.detail', ['hutang' => $hutang, 'logHutang' => $logHutang]);
    }

    public function bayarHutang(Request $request)
    {
        $bayar = new LogHutang;
        $bayar->log_hutang_dibayar = $request->tb_bayar;
        $bayar->hutang_id = $request->tb_hutang_id;
        $bayar->save();

        $hutang = Hutang::find($request->tb_hutang_id);
        $hutang->hutang_dibayar += $request->tb_bayar;
        if ($hutang->hutang_dibayar >= $hutang->hutang_total) {
            $hutang->hutang_status = 'lunas';
        }
        $hutang->save();

        return redirect()->back()->with('message', 'pembayaran hutang berhasil');
    }
}

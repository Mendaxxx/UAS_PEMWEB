<?php

namespace App\Http\Controllers;

use App\Barang;
use App\DetailTransaksi;
use App\LogBarang;
use App\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KasirDuaController extends Controller
{
    public function index()
    {
        # code...
        return view('kasir.option');
    }

    public function newOrder()
    {
        $idTransaksi = DB::table('transaksis')->insertGetId([
            'total_transaksi' => 0,
            'bayar_transaksi' => 0,
            'kembali_transaksi' => 0,
            'total_item_transaksi' => 0,
            'total_qty_transaksi' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $transaksi = Transaksi::find($idTransaksi);
        $transaksi->kode_transaksi = now()->format('Ymd') + $idTransaksi;
        $transaksi->save();

        return redirect()->route('ksr.order', ['id' => $idTransaksi]);
    }

    public function viewOrder($id)
    {
        $barangs = Barang::all();
        $transaksi = Transaksi::find($id);
        $detailTransaksis = DB::table('detail_transaksis')
            ->join('barangs', 'detail_transaksis.barang_id', '=', 'barangs.id')
            ->select('barangs.*', 'detail_transaksis.*', 'barangs.id AS bid', 'detail_transaksis.id AS dtid')
            ->where('detail_transaksis.transaksi_id', $id)
            ->get();
        return view('kasir.kasir', [
            'barangs' => $barangs,
            'transaksi' => $transaksi,
            'detailTransaksis' => $detailTransaksis,
        ]);
    }

    public function addItem(Request $request, $id)
    {
        $validatedData = $request->validate([
            'tb_barang_jumlah' => 'required',
        ]);

        if ($request->tb_barang_jumlah > $request->tb_barang_stok) {
            return redirect()->back()->with('warning', 'jumlah yang dimasukan melebihi stok.');
        }

        $transaksi = Transaksi::find($id);
        $transaksi->total_transaksi += $request->tb_barang_subtotal * $request->tb_barang_jumlah;
        $transaksi->total_qty_transaksi += $request->tb_barang_jumlah;
        $transaksi->total_item_transaksi += 1;
        $transaksi->save();

        $barang = Barang::find($request->tb_barang_id);
        $barang->qty_barang -= $request->tb_barang_jumlah;
        $barang->save();

        $order = new DetailTransaksi;
        $order->transaksi_id = $id;
        $order->barang_id = $request->tb_barang_id;
        $order->item_qty = $request->tb_barang_jumlah;
        $order->subtotal = $request->tb_barang_subtotal * $request->tb_barang_jumlah;
        $order->save();

        $logBarang = new LogBarang;
        $logBarang->barang_id = $request->tb_barang_id;
        $logBarang->log_barang_status = 'keluar (masuk keranjang)';
        $logBarang->log_barang_jumlah = $request->tb_barang_jumlah;
        $logBarang->save();

        return redirect()->route('ksr.order', ['id' => $id]);
    }

    public function deleteItem(Request $request, $id)
    {
        $detailTransaksi = DetailTransaksi::find($id);

        $barang = Barang::find($request->id_barang);
        $barang->qty_barang += $request->tb_item_qty;
        $barang->save();

        $transaksi = Transaksi::find($request->id_Transaksi);
        $transaksi->total_transaksi -= $detailTransaksi->subtotal;
        $transaksi->total_qty_transaksi -= $request->tb_item_qty;
        $transaksi->total_item_transaksi -= 1;
        $transaksi->save();

        $logBarang = new LogBarang;
        $logBarang->barang_id = $request->id_barang;
        $logBarang->log_barang_status = 'masuk (dihapus dari keranjang)';
        $logBarang->log_barang_jumlah = $request->tb_item_qty;
        $logBarang->save();

        $detailTransaksi->delete();
        return redirect()->back()->with('success', ' berhasil dihapus');
    }

    public function KonfirmasiPembayaran($id)
    {
        $transaksi = Transaksi::find($id);
        $detailTransaksis = DB::table('detail_transaksis')
            ->join('barangs', 'detail_transaksis.barang_id', '=', 'barangs.id')
            ->select('barangs.*', 'detail_transaksis.*', 'barangs.id AS bid', 'detail_transaksis.id AS dtid')
            ->where('detail_transaksis.transaksi_id', $id)
            ->get();
        return view('kasir.pembayaran  ', ['transaksi' => $transaksi, 'detailTransaksis' => $detailTransaksis]);
    }

    public function batalkanTransaksi($id)
    {
        $detailTransaksi = DetailTransaksi::where('transaksi_id', $id)->get();
        foreach ($detailTransaksi as $detail => $value) {
            $barang = Barang::find($value->barang_id);
            $barang->qty_barang += $value->item_qty;
            $barang->save();
            $value->delete();
        }
        $transaksi = Transaksi::find($id);
        $transaksi->delete();
        return redirect()->route('landing')->with('message', 'transaksi berhasil dibatalkan');
    }

    public function ProsesPembayaran(Request $request, $id)
    {
        $transaksi = Transaksi::find($id);
        if ($request->tb_transaksi_dibayar < $transaksi->total_transaksi) {
            return redirect()->back()->with('message', 'uang dibayar kurang dari total harga');
        }

        $transaksi->bayar_transaksi = $request->tb_transaksi_dibayar;
        $transaksi->kembali_transaksi = $request->tb_transaksi_kembali;
        $transaksi->status_transaksi = 'dibayar';
        $transaksi->jenis_transaksi = $request->cbJenisPembayaran;

        $transaksi->save();
        return redirect()->back()->with('message', 'transaksi berhasil dibayar');
    }

    public function printReceipt($id)
    {
        $transaksi = Transaksi::find($id);
        $detailTransaksis = DB::table('detail_transaksis')
            ->join('barangs', 'detail_transaksis.barang_id', '=', 'barangs.id')
            ->select('barangs.*', 'detail_transaksis.*', 'barangs.id AS bid', 'detail_transaksis.id AS dtid')
            ->where('detail_transaksis.transaksi_id', $id)
            ->get();
        return view('kasir.receipt  ', ['transaksi' => $transaksi, 'detailTransaksis' => $detailTransaksis]);
    }
}

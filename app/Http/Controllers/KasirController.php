<?php

namespace App\Http\Controllers;

use App\Barang;
use App\DetailTransaksi;
use App\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KasirController extends Controller
{
    //
    public function index()
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
        return redirect()->route('kasir.order', ['id' => $idTransaksi]);
    }

    public function kasirUtama($id)
    {
        $barangs = Barang::all();
        $transaksi = Transaksi::find($id);
        $detailTransaksis = DB::table('detail_transaksis')
            ->join('barangs', 'detail_transaksis.barang_id', '=', 'barangs.id')
            ->select('barangs.*', 'detail_transaksis.*', 'barangs.id AS bid', 'detail_transaksis.id AS dtid')
            ->where('detail_transaksis.transaksi_id', $id)
            ->get();
        return view('kasir.index', [
            'barangs' => $barangs,
            'transaksi' => $transaksi,
            'detailTransaksis' => $detailTransaksis,
        ]);
    }

    public function addKasirOrder(Request $request, $id)
    {
        $order = new DetailTransaksi;
        $order->barang_id = $request->tb_barang_id;
        $order->transaksi_id = $id;
        $order->item_qty = 1;
        $order->subtotal = $request->tb_barang_subtotal;
        $order->save();

        $barang = Barang::find($request->tb_barang_id);
        $barang->qty_barang -= 1;
        $barang->save();

        $transaksi = Transaksi::find($id);
        $transaksi->total_item_transaksi += 1;
        $transaksi->total_qty_transaksi += 1;
        $transaksi->total_transaksi += $request->tb_barang_subtotal;
        $transaksi->save();

        return redirect()->route('kasir.order', ['id' => $id]);
    }

    public function deleteKasirOrder(Request $request, $id)
    {
        $transaksi = Transaksi::find($request->id_Transaksi);
        $order = DetailTransaksi::find($id);

        $transaksi->total_transaksi -= $order->item_qty * $order->subtotal;
        $transaksi->total_item_transaksi -= 1;
        $transaksi->total_qty_transaksi -= $order->item_qty;
        $transaksi->save();

        $barang = Barang::find($order->barang_id);
        $barang->qty_barang += $order->item_qty;
        $barang->save();

        DB::table('detail_transaksis')->where('id', '=', $id)->delete();
        return redirect()->back()->with('success', ' berhasil dihapus');
    }

    public function increaseKasirOrder(Request $request, $id)
    {
        $order = DetailTransaksi::find($id);
        $transaksi = Transaksi::find($request->tb_transaksi_id);
        $barang = Barang::find($order->barang_id);

        $transaksi->total_qty_transaksi += 1;
        $transaksi->total_transaksi += $order->subtotal;
        $transaksi->save();

        $order->item_qty = $request->tb_detail_qty;
        $order->save();

        $barang->qty_barang -= 1;
        $barang->save();
        return response()->json([$order, $transaksi]);
    }

    public function decreaseKasirOrder(Request $request, $id)
    {
        $order = DetailTransaksi::find($id);
        $transaksi = Transaksi::find($request->tb_transaksi_id);
        $barang = Barang::find($order->barang_id);

        $transaksi->total_qty_transaksi -= 1;
        $transaksi->total_transaksi -= $order->subtotal;
        $transaksi->save();

        $order->item_qty = $request->tb_detail_qty;
        $order->save();

        $barang->qty_barang += 1;
        $barang->save();
        return response()->json([$order, $transaksi]);
    }

    public function batalTransaksi($id)
    {
        DB::table('transaksis')->where('id', '=', $id)->delete();
        return redirect()->route('landing')->with('message', 'transaksi berhasil dibatalkan');
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

    public function searchBarang(Request $request, $id)
    {
        // dd($request->tb_cari_barang);
        $barangs = Barang::where('nama_barang', 'LIKE', '%' . $request->tb_cari_barang . '%')->get();
        $transaksi = Transaksi::find($id);
        $detailTransaksis = DB::table('detail_transaksis')
            ->join('barangs', 'detail_transaksis.barang_id', '=', 'barangs.id')
            ->select('barangs.*', 'detail_transaksis.*', 'barangs.id AS bid', 'detail_transaksis.id AS dtid')
            ->where('detail_transaksis.transaksi_id', $id)
            ->get();
        return view('kasir.index', [
            'barangs' => $barangs,
            'transaksi' => $transaksi,
            'detailTransaksis' => $detailTransaksis,
        ]);
    }
}

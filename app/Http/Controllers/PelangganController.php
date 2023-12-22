<?php

namespace App\Http\Controllers;

use App\Pelanggan;
use Illuminate\Http\Request;

class PelangganController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $pelanggan = Pelanggan::all();
        return view('pelanggan.index', ['pelanggan' => $pelanggan]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $pelanggan = new Pelanggan;
        $pelanggan->nama_pelanggan = $request->tb_nama_pelanggan;
        $pelanggan->kontak_pelanggan = $request->tb_kontak_pelanggan;
        $pelanggan->save();
        return redirect()->back()->with('message', 'data berhasil ditambahkan');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $pelanggan = Pelanggan::find($id);
        return response()->json($pelanggan);
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
        $pelanggan = Pelanggan::find($id);
        $pelanggan->nama_pelanggan = $request->tbNamaPelanggan;
        $pelanggan->kontak_pelanggan = $request->tbKontakPelanggan;
        $pelanggan->save();
        return redirect()->back()->with('message', 'data berhasil diperbarui');
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
        $pelanggan = Pelanggan::find($id);
        $pelanggan->delete();
        return redirect()->back()->with('message', 'data berhasil dihapus');
    }
}

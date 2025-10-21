<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Lokasi;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class BarangController extends Controller implements HasMiddleware
{
    public static function middleware() {
        return [
            new middleware('permission:manage barang', except: ['destroy']),
            new middleware('permission:delete barang', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $search = $request->search;

        $barangs = Barang::with(['kategori', 'lokasi'])
            ->when($search, function ($querry, $search) {
                $querry->where('nama_barang', 'like', '%' . $search . '%')
                    ->orWhere('kode_barang', 'like', '%' . $search . '%');
        })
        ->latest()->paginate()->withQueryString ();
        
        return view('barang.index', compact('barangs'));
    }

    public function create()
    {
        $kategori = Kategori::all();
        $lokasi = Lokasi::all();
        $barang = new Barang ();

        return view('barang.create', compact ('barang', 'kategori', 'lokasi'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate ([
            'kode_barang' => 'required|string|max:50|unique:barangs,kode_barang',
            'nama_barang' => 'required|string|max:150',
            'kategori_id' => 'required|exists:kategoris,id',
            'lokasi_id' => 'required|exists:lokasis,id',
            'jumlah' => 'required_if:is_per_unit,0|integer|min:0',
            'satuan' => 'required|string|max:20',
            'is_per_unit' => 'boolean',
            'dapat_dipinjam' => 'boolean',
            'kondisi' => 'required|in:Baik,Rusak ringan,Rusak berat',
            'sumber_dana' => 'required|in:Pemerintah,Swadaya,Donatur',
            'tanggal_pengadaan' => 'required|date',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Jika barang per unit, set jumlah = 0 (akan dihitung dari detail_barangs)
        if ($request->has('is_per_unit') && $request->is_per_unit) {
            $validated['jumlah'] = 0;
            $validated['is_per_unit'] = true;
        } else {
            $validated['is_per_unit'] = false;
        }

        // Set dapat_dipinjam
        $validated['dapat_dipinjam'] = $request->has('dapat_dipinjam') ? true : false;

        if ($request->hasFile('gambar')) {
            $validated['gambar'] = $request->file('gambar')->store(null, 'gambar-barang');
        }

        $barang = Barang::create($validated);

        // Redirect ke kelola unit jika barang per unit
        if ($barang->is_per_unit) {
            return redirect()->route('barang.units.index', $barang)
                ->with('success', 'Data barang berhasil ditambahkan. Silakan tambahkan unit barang.');
        }

        return redirect()->route('barang.index')
            ->with('success', 'Data barang berhasil ditambahkan.');
    }

    public function show(Barang $barang)
    {
        $barang->load(['kategori', 'lokasi']);

        return view('barang.show', compact('barang'));
    }

    public function edit(Barang $barang)
    {
        $kategori = Kategori::all();
        $lokasi  = Lokasi::all();

        return view('barang.edit', compact('barang', 'kategori', 'lokasi'));
    }

    public function update(Request $request, Barang $barang)
    {
        $validated = $request->validate([
            'kode_barang' => 'required|string|max:50|unique:barangs,kode_barang,' . $barang->id,
            'nama_barang' => 'required|string|max:150',
            'kategori_id' => 'required|exists:kategoris,id',
            'lokasi_id' => 'required|exists:lokasis,id',
            'jumlah' => 'required_if:is_per_unit,0|integer|min:0',
            'satuan' => 'required|string|max:20',
            'is_per_unit' => 'boolean',
            'dapat_dipinjam' => 'boolean', // TAMBAHKAN INI
            'kondisi' => 'required|in:Baik,Rusak ringan,Rusak berat',
            'sumber_dana' => 'required|in:Pemerintah,Swadaya,Donatur',
            'tanggal_pengadaan' => 'required|date',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle perubahan dari per unit ke bukan per unit atau sebaliknya
        if ($request->has('is_per_unit') && $request->is_per_unit) {
            $validated['jumlah'] = 0;
            $validated['is_per_unit'] = true;
        } else {
            $validated['is_per_unit'] = false;
        }

        // TAMBAHKAN INI: Set dapat_dipinjam
        $validated['dapat_dipinjam'] = $request->has('dapat_dipinjam') ? true : false;

        if ($request->hasFile('gambar')) {
            if ($barang->gambar) {
                Storage::disk('gambar-barang')->delete($barang->gambar);
            }

            $validated['gambar'] = $request->file('gambar')->store(null, 'gambar-barang');
        }

        $barang->update($validated);

        return redirect()->route('barang.index')->with('success', 'Data barang berhasil diperbarui.');
    }

    public function destroy(Barang $barang)
    {
        if ($barang->gambar) {
            Storage::disk('gambar-barang')->delete($barang->gambar);
        }
        $barang->delete();
        return redirect()->route('barang.index')->with('success', 'Data barang berhasil dihapus.');
    }

    public function cetakLaporan() {
        $barangs = Barang::with(['kategori', 'lokasi'])->get();
        $data = [
            'title' => 'Laporan Data Barang Inventaris',
            'date' => date('d F Y'),
            'barangs' => $barangs
        ];

        $pdf = Pdf::loadView('barang.laporan', $data);
        return $pdf->stream('laporan-inventaris-barang.pdf');
    }
}
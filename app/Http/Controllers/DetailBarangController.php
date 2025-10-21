<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\DetailBarang;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class DetailBarangController extends Controller implements HasMiddleware
{
    public static function middleware()
    {
        return [
            new Middleware('permission:manage barang', except: ['destroy']),
            new Middleware('permission:delete barang', only: ['destroy']),
        ];
    }

    /**
     * Halaman kelola unit untuk barang tertentu
     */
    public function index(Barang $barang)
    {
        $barang->load(['detailBarangs.peminjamanAktif', 'detailBarangs.lokasi', 'kategori', 'lokasi']);
        $lokasis = \App\Models\Lokasi::all();
        
        return view('barang.kelola-unit', compact('barang', 'lokasis'));
    }

    /**
     * Tambah unit baru (single)
     */
    public function store(Request $request, Barang $barang)
    {
        $validated = $request->validate([
            'sub_kode' => 'required|string|max:50|unique:detail_barangs,sub_kode',
            'lokasi_id' => 'required|exists:lokasis,id',
            'kondisi' => 'required|in:Baik,Rusak ringan,Rusak berat',
            'status' => 'required|in:Tersedia,Rusak',
            'keterangan' => 'nullable|string',
        ]);

        $barang->detailBarangs()->create($validated);

        return back()->with('success', 'Unit barang berhasil ditambahkan.');
    }

    /**
     * Tambah multi unit sekaligus
     */
    public function storeMultiple(Request $request, Barang $barang)
    {
        $validated = $request->validate([
            'jumlah_unit' => 'required|integer|min:1|max:500',
            'prefix_kode' => 'required|string|max:50',
            'lokasi_id' => 'required|exists:lokasis,id',
            'kondisi' => 'required|in:Baik,Rusak ringan,Rusak berat',
            'status' => 'required|in:Tersedia,Rusak',
            'keterangan' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Cari nomor urut terakhir untuk barang ini
            $lastUnit = DetailBarang::where('barang_id', $barang->id)
                ->where('sub_kode', 'like', $validated['prefix_kode'] . '%')
                ->orderBy('sub_kode', 'desc')
                ->first();

            // Tentukan nomor mulai
            $startNumber = 1;
            if ($lastUnit) {
                // Extract nomor dari kode terakhir (contoh: ALB002-005 -> 5)
                $lastCode = $lastUnit->sub_kode;
                $parts = explode('-', $lastCode);
                if (count($parts) > 1) {
                    $lastNumber = (int) end($parts);
                    $startNumber = $lastNumber + 1;
                }
            }

            // Generate dan insert multiple units
            $units = [];
            $jumlah = $validated['jumlah_unit'];

            for ($i = 0; $i < $jumlah; $i++) {
                $unitNumber = str_pad($startNumber + $i, 3, '0', STR_PAD_LEFT);
                $subKode = $validated['prefix_kode'] . '-' . $unitNumber;

                // Cek apakah kode sudah ada
                $exists = DetailBarang::where('sub_kode', $subKode)->exists();
                if ($exists) {
                    throw new \Exception("Kode unit {$subKode} sudah ada. Proses dibatalkan.");
                }

                $units[] = [
                    'barang_id' => $barang->id,
                    'sub_kode' => $subKode,
                    'lokasi_id' => $validated['lokasi_id'],
                    'kondisi' => $validated['kondisi'],
                    'status' => $validated['status'],
                    'keterangan' => $validated['keterangan'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Bulk insert
            DetailBarang::insert($units);

            DB::commit();

            return back()->with('success', "Berhasil menambahkan {$jumlah} unit barang.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menambahkan unit: ' . $e->getMessage());
        }
    }

    /**
     * Update unit
     */
    public function update(Request $request, Barang $barang, DetailBarang $detailBarang)
    {
        // Pastikan detail barang ini milik barang yang dipilih
        if ($detailBarang->barang_id !== $barang->id) {
            return back()->with('error', 'Unit tidak ditemukan.');
        }

        $validated = $request->validate([
            'sub_kode' => 'required|string|max:50|unique:detail_barangs,sub_kode,' . $detailBarang->id,
            'lokasi_id' => 'required|exists:lokasis,id',
            'kondisi' => 'required|in:Baik,Rusak ringan,Rusak berat',
            'status' => 'required|in:Tersedia,Dipinjam,Rusak',
            'keterangan' => 'nullable|string',
        ]);

        $detailBarang->update($validated);

        return back()->with('success', 'Unit barang berhasil diperbarui.');
    }

    /**
     * Hapus unit
     */
    public function destroy(Barang $barang, DetailBarang $detailBarang)
    {
        // Pastikan detail barang ini milik barang yang dipilih
        if ($detailBarang->barang_id !== $barang->id) {
            return back()->with('error', 'Unit tidak ditemukan.');
        }

        // Cek apakah unit sedang dipinjam
        if ($detailBarang->status === 'Dipinjam') {
            return back()->with('error', 'Unit sedang dipinjam, tidak dapat dihapus.');
        }

        $detailBarang->delete();

        return back()->with('success', 'Unit barang berhasil dihapus.');
    }
}
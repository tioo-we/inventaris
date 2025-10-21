<?php

namespace App\Http\Controllers;

use App\Models\Peminjaman;
use App\Models\DetailBarang;
use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class PeminjamanController extends Controller implements HasMiddleware
{
    public static function middleware()
    {
        return [
            new Middleware('permission:manage barang', except: ['destroy']),
            new Middleware('permission:delete barang', only: ['destroy']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->search;
        $status = $request->status;

        $peminjamans = Peminjaman::with(['detailBarang.barang'])
            ->when($search, function ($query, $search) {
                $query->where('nama_peminjam', 'like', '%' . $search . '%')
                    ->orWhereHas('detailBarang', function ($q) use ($search) {
                        $q->where('sub_kode', 'like', '%' . $search . '%')
                            ->orWhereHas('barang', function ($q2) use ($search) {
                                $q2->where('nama_barang', 'like', '%' . $search . '%');
                            });
                    });
            })
            ->when($status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate()
            ->withQueryString();

        return view('peminjaman.index', compact('peminjamans'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // PENTING: Hanya ambil barang yang:
        // 1. dapat_dipinjam = true (barang boleh dipinjam)
        // 2. is_per_unit = true (barang dikelola per unit/detail)
        // 3. Memiliki detail barang dengan status Tersedia
        $barangs = Barang::where('dapat_dipinjam', true)
            ->where('is_per_unit', true)
            ->whereHas('detailBarangs', function ($query) {
                $query->where('status', 'Tersedia');
            })
            ->with(['detailBarangs' => function ($query) {
                $query->where('status', 'Tersedia');
            }])
            ->get();

        $peminjaman = new Peminjaman();

        return view('peminjaman.create', compact('peminjaman', 'barangs'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'detail_barang_id' => 'required|exists:detail_barangs,id',
            'nama_peminjam' => 'required|string|max:150',
            'kontak' => 'nullable|string|max:50',
            'keperluan' => 'nullable|string',
            'tanggal_pinjam' => 'required|date',
            'catatan' => 'nullable|string',
        ]);

        // Gunakan database transaction untuk mencegah race condition
        try {
            DB::beginTransaction();

            // Gunakan lockForUpdate untuk mencegah double booking
            $detailBarang = DetailBarang::with('barang')
                ->lockForUpdate()
                ->findOrFail($validated['detail_barang_id']);
            
            // Validasi 1: Cek apakah barang dapat dipinjam
            if (!$detailBarang->barang->dapat_dipinjam) {
                DB::rollBack();
                return back()
                    ->withInput()
                    ->with('error', 'Barang ini tidak dapat dipinjam. Silakan pilih barang lain.');
            }

            // Validasi 2: Cek apakah barang harus per unit
            if (!$detailBarang->barang->is_per_unit) {
                DB::rollBack();
                return back()
                    ->withInput()
                    ->with('error', 'Barang ini tidak dikelola per unit. Tidak dapat dipinjam melalui sistem ini.');
            }

            // Validasi 3: Cek apakah detail barang masih tersedia
            if ($detailBarang->status !== 'Tersedia') {
                DB::rollBack();
                return back()
                    ->withInput()
                    ->with('error', 'Barang tidak tersedia untuk dipinjam. Mungkin sudah dipinjam oleh orang lain.');
            }

            // Validasi 4: Cek apakah ada peminjaman aktif untuk detail barang ini
            $existingPeminjaman = Peminjaman::where('detail_barang_id', $validated['detail_barang_id'])
                ->where('status', 'Dipinjam')
                ->exists();

            if ($existingPeminjaman) {
                DB::rollBack();
                return back()
                    ->withInput()
                    ->with('error', 'Barang sedang dipinjam. Silakan pilih barang lain atau tunggu hingga dikembalikan.');
            }

            $validated['status'] = 'Dipinjam';

            // Buat peminjaman
            Peminjaman::create($validated);

            // Update status detail barang
            $detailBarang->update(['status' => 'Dipinjam']);

            DB::commit();

            return redirect()->route('peminjaman.index')
                ->with('success', 'Data peminjaman berhasil ditambahkan.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Peminjaman $peminjaman)
    {
        $peminjaman->load(['detailBarang.barang']);
        return view('peminjaman.show', compact('peminjaman'));
    }

    /**
     * Kembalikan barang yang dipinjam
     */
    public function kembalikan(Request $request, Peminjaman $peminjaman)
    {
        // Validasi: Cek apakah barang sudah dikembalikan
        if ($peminjaman->status === 'Dikembalikan') {
            return back()->with('error', 'Barang sudah dikembalikan sebelumnya.');
        }

        // Validasi: Cek apakah status adalah Dipinjam
        if ($peminjaman->status !== 'Dipinjam') {
            return back()->with('error', 'Status peminjaman tidak valid untuk pengembalian.');
        }

        $validated = $request->validate([
            'tanggal_kembali' => 'required|date',
            'catatan' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $validated['status'] = 'Dikembalikan';

            // Update peminjaman
            $peminjaman->update($validated);

            // Update status detail barang menjadi tersedia
            $peminjaman->detailBarang->update(['status' => 'Tersedia']);

            DB::commit();

            return redirect()->route('peminjaman.index')
                ->with('success', 'Barang berhasil dikembalikan.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Peminjaman $peminjaman)
    {
        try {
            DB::beginTransaction();

            // Jika masih dipinjam, kembalikan status barang
            if ($peminjaman->status === 'Dipinjam') {
                $peminjaman->detailBarang->update(['status' => 'Tersedia']);
            }

            $peminjaman->delete();

            DB::commit();

            return redirect()->route('peminjaman.index')
                ->with('success', 'Data peminjaman berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->with('error', 'Terjadi kesalahan saat menghapus: ' . $e->getMessage());
        }
    }
}
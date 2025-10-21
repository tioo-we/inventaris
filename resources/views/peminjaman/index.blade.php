<x-main-layout title-page="Data Peminjaman Barang">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Daftar Peminjaman</h5>
            <a href="{{ route('peminjaman.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Tambah Peminjaman
            </a>
        </div>

        <div class="card-body">
            {{-- Alert Messages --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- Search & Filter --}}
            <form method="GET" class="row g-3 mb-4">
                <div class="col-md-6">
                    <input type="text" class="form-control" name="search" placeholder="Cari peminjam / barang..."
                        value="{{ request('search') }}">
                </div>
                <div class="col-md-4">
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="Dipinjam" {{ request('status') === 'Dipinjam' ? 'selected' : '' }}>Dipinjam</option>
                        <option value="Dikembalikan" {{ request('status') === 'Dikembalikan' ? 'selected' : '' }}>Dikembalikan</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary w-100">
                        <i class="bi bi-search"></i> Cari
                    </button>
                </div>
            </form>

            {{-- Table --}}
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Nama Barang</th>
                            <th>Kode Unit</th>
                            <th>Peminjam</th>
                            <th>Tanggal Pinjam</th>
                            <th>Tanggal Kembali</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($peminjamans as $index => $peminjaman)
                            <tr>
                                <td>{{ $peminjamans->firstItem() + $index }}</td>
                                <td>{{ $peminjaman->detailBarang->barang->nama_barang }}</td>
                                <td>
                                    <span class="badge bg-secondary">{{ $peminjaman->detailBarang->sub_kode }}</span>
                                </td>
                                <td>
                                    <strong>{{ $peminjaman->nama_peminjam }}</strong>
                                    @if ($peminjaman->kontak)
                                        <br><small class="text-muted">{{ $peminjaman->kontak }}</small>
                                    @endif
                                </td>
                                <td>{{ $peminjaman->tanggal_pinjam->format('d/m/Y') }}</td>
                                <td>
                                    @if ($peminjaman->tanggal_kembali)
                                        {{ $peminjaman->tanggal_kembali->format('d/m/Y') }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($peminjaman->status === 'Dipinjam')
                                        <span class="badge bg-warning text-dark">Dipinjam</span>
                                    @else
                                        <span class="badge bg-success">Dikembalikan</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        @if ($peminjaman->status === 'Dipinjam')
                                            <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                                data-bs-target="#modalKembalikan{{ $peminjaman->id }}">
                                                <i class="bi bi-arrow-return-left"></i> Kembalikan
                                            </button>
                                        @endif
                                        
                                        <button type="button" class="btn btn-danger mx-1" data-bs-toggle="modal"
                                            data-bs-target="#deleteModal" data-url="{{ route('peminjaman.destroy', $peminjaman) }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            {{-- Modal Kembalikan --}}
                            @if ($peminjaman->status === 'Dipinjam')
                                <div class="modal fade" id="modalKembalikan{{ $peminjaman->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('peminjaman.kembalikan', $peminjaman) }}">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Kembalikan Barang</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Tanggal Kembali <span class="text-danger">*</span></label>
                                                        <input type="date" name="tanggal_kembali" class="form-control" 
                                                            value="{{ date('Y-m-d') }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Catatan</label>
                                                        <textarea name="catatan" class="form-control" rows="3" placeholder="Kondisi saat dikembalikan..."></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-success">Kembalikan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    Tidak ada data peminjaman
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="d-flex justify-content-center">
                {{ $peminjamans->links() }}
            </div>
        </div>
    </div>
</x-main-layout>
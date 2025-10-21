<x-main-layout title-page="Kelola Unit Barang">
    {{-- Info Barang --}}
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h5 class="mb-2">
                        <i class="bi bi-box"></i> {{ $barang->nama_barang }}
                    </h5>
                    <div class="text-muted">
                        <span class="me-3"><strong>Kode:</strong> {{ $barang->kode_barang }}</span>
                        <span class="me-3"><strong>Kategori:</strong> {{ $barang->kategori->nama_kategori }}</span>
                        <span class="me-3"><strong>Lokasi:</strong> {{ $barang->lokasi->nama_lokasi }}</span>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <x-tombol-kembali :href="route('barang.index')" />
                </div>
            </div>
        </div>
    </div>

    {{-- Alert Messages --}}
    <x-notif-alert class="mb-4" />

    {{-- Form Tambah Unit Baru --}}
    <div class="card mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="bi bi-plus-circle"></i> Tambah Unit Baru</h6>
            <button type="button" class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#multiUnitModal">
                <i class="bi bi-stack"></i> Tambah Multi Unit
            </button>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('barang.units.store', $barang) }}">
                @csrf
                <div class="row">
                    <div class="col-md-3">
                        <x-form-input label="Kode Unit" name="sub_kode" 
                            placeholder="Contoh: {{ $barang->kode_barang }}-001" required />
                        <small class="text-muted">Format: {{ $barang->kode_barang }}-XXX</small>
                    </div>
                    <div class="col-md-2">
                        <x-form-select label="Lokasi" name="lokasi_id" :option-data="$lokasis" 
                            option-label="nama_lokasi" option-value="id" required />
                    </div>
                    <div class="col-md-2">
                        @php
                            $kondisiOptions = [
                                ['kondisi' => 'Baik'], 
                                ['kondisi' => 'Rusak ringan'], 
                                ['kondisi' => 'Rusak berat']
                            ];
                        @endphp
                        <x-form-select label="Kondisi" name="kondisi" :option-data="$kondisiOptions" 
                            option-label="kondisi" option-value="kondisi" value="Baik" required />
                    </div>
                    <div class="col-md-2">
                        @php
                            $statusOptions = [
                                ['status' => 'Tersedia'], 
                                ['status' => 'Rusak']
                            ];
                        @endphp
                        <x-form-select label="Status" name="status" :option-data="$statusOptions" 
                            option-label="status" option-value="status" value="Tersedia" required />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Keterangan</label>
                        <div class="input-group">
                            <input type="text" name="keterangan" class="form-control" 
                                placeholder="Keterangan...">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Simpan
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Daftar Unit --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">
                <i class="bi bi-list-ul"></i> Daftar Unit ({{ $barang->detailBarangs->count() }} Unit)
            </h6>
            <div>
                @php
                    $tersedia = $barang->detailBarangs->where('status', 'Tersedia')->count();
                    $dipinjam = $barang->detailBarangs->where('status', 'Dipinjam')->count();
                    $rusak = $barang->detailBarangs->where('status', 'Rusak')->count();
                @endphp
                <span class="badge bg-success">{{ $tersedia }} Tersedia</span>
                <span class="badge bg-warning text-dark">{{ $dipinjam }} Dipinjam</span>
                <span class="badge bg-danger">{{ $rusak }} Rusak</span>
            </div>
        </div>

        <div class="card-body p-0">
            @if ($barang->detailBarangs->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">No</th>
                                <th width="15%">Kode Unit</th>
                                <th width="12%">Kondisi</th>
                                <th width="12%">Status</th>
                                <th width="25%">Keterangan</th>
                                <th width="20%">Info Peminjaman</th>
                                <th width="11%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($barang->detailBarangs as $index => $unit)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <strong class="text-primary">{{ $unit->sub_kode }}</strong>
                                    </td>
                                    <td>
                                        @php
                                            $badgeClass = 'bg-success';
                                            if ($unit->kondisi == 'Rusak ringan') $badgeClass = 'bg-warning text-dark';
                                            if ($unit->kondisi == 'Rusak berat') $badgeClass = 'bg-danger';
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">{{ $unit->kondisi }}</span>
                                    </td>
                                    <td>
                                        @if ($unit->status === 'Tersedia')
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle"></i> Tersedia
                                            </span>
                                        @elseif ($unit->status === 'Dipinjam')
                                            <span class="badge bg-warning text-dark">
                                                <i class="bi bi-clock"></i> Dipinjam
                                            </span>
                                        @else
                                            <span class="badge bg-danger">
                                                <i class="bi bi-x-circle"></i> Rusak
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $unit->keterangan ?? '-' }}</small>
                                    </td>
                                    <td>
                                        @if ($unit->peminjamanAktif)
                                            <small>
                                                <i class="bi bi-person-fill text-warning"></i> 
                                                <strong>{{ $unit->peminjamanAktif->nama_peminjam }}</strong><br>
                                                <i class="bi bi-calendar-event"></i> 
                                                {{ $unit->peminjamanAktif->tanggal_pinjam->format('d/m/Y') }}
                                            </small>
                                        @else
                                            <small class="text-muted">-</small>
                                        @endif
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-warning" 
                                            data-bs-toggle="modal" data-bs-target="#editModal{{ $unit->id }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        
                                        @can('delete barang')
                                            @if ($unit->status !== 'Dipinjam')
                                                <button type="button" class="btn btn-sm btn-danger" 
                                                    data-bs-toggle="modal" data-bs-target="#deleteModal"
                                                    data-url="{{ route('barang.units.destroy', [$barang, $unit]) }}">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            @endif
                                        @endcan
                                    </td>
                                </tr>

                                {{-- Modal Edit --}}
                                <div class="modal fade" id="editModal{{ $unit->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('barang.units.update', [$barang, $unit]) }}">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Unit: {{ $unit->sub_kode }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Kode Unit <span class="text-danger">*</span></label>
                                                        <input type="text" name="sub_kode" class="form-control" 
                                                            value="{{ $unit->sub_kode }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Lokasi <span class="text-danger">*</span></label>
                                                        <select name="lokasi_id" class="form-select" required>
                                                            @foreach ($lokasis as $lok)
                                                                <option value="{{ $lok->id }}" {{ $unit->lokasi_id == $lok->id ? 'selected' : '' }}>
                                                                    {{ $lok->nama_lokasi }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Kondisi <span class="text-danger">*</span></label>
                                                        <select name="kondisi" class="form-select" required>
                                                            <option value="Baik" {{ $unit->kondisi == 'Baik' ? 'selected' : '' }}>Baik</option>
                                                            <option value="Rusak ringan" {{ $unit->kondisi == 'Rusak ringan' ? 'selected' : '' }}>Rusak ringan</option>
                                                            <option value="Rusak berat" {{ $unit->kondisi == 'Rusak berat' ? 'selected' : '' }}>Rusak berat</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Status <span class="text-danger">*</span></label>
                                                        <select name="status" class="form-select" required>
                                                            <option value="Tersedia" {{ $unit->status == 'Tersedia' ? 'selected' : '' }}>Tersedia</option>
                                                            <option value="Dipinjam" {{ $unit->status == 'Dipinjam' ? 'selected' : '' }}>Dipinjam</option>
                                                            <option value="Rusak" {{ $unit->status == 'Rusak' ? 'selected' : '' }}>Rusak</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Keterangan</label>
                                                        <textarea name="keterangan" class="form-control" rows="3">{{ $unit->keterangan }}</textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-inbox fs-1 text-muted d-block mb-2"></i>
                    <p class="text-muted">Belum ada unit untuk barang ini. Tambahkan unit baru di atas.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Modal Tambah Multi Unit --}}
    <div class="modal fade" id="multiUnitModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="{{ route('barang.units.store-multiple', $barang) }}" id="multiUnitForm">
                    @csrf
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="bi bi-stack"></i> Tambah Multi Unit Sekaligus
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> 
                            <strong>Info:</strong> Sistem akan otomatis generate kode unit secara berurutan.
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Prefix Kode <span class="text-danger">*</span></label>
                                <input type="text" name="prefix_kode" class="form-control" 
                                    value="{{ $barang->kode_barang }}" required readonly>
                                <small class="text-muted">Kode akan di-generate: {{ $barang->kode_barang }}-001, -002, dst.</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Jumlah Unit <span class="text-danger">*</span></label>
                                <input type="number" name="jumlah_unit" id="jumlahUnit" class="form-control" 
                                    min="1" max="500" required placeholder="Contoh: 50">
                                <small class="text-muted">Maksimal 500 unit per batch</small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Lokasi <span class="text-danger">*</span></label>
                                <select name="lokasi_id" class="form-select" required>
                                    <option value="">Pilih Lokasi</option>
                                    @foreach ($lokasis as $lok)
                                        <option value="{{ $lok->id }}" {{ $lok->id == $barang->lokasi_id ? 'selected' : '' }}>
                                            {{ $lok->nama_lokasi }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kondisi <span class="text-danger">*</span></label>
                                <select name="kondisi" class="form-select" required>
                                    <option value="Baik" selected>Baik</option>
                                    <option value="Rusak ringan">Rusak ringan</option>
                                    <option value="Rusak berat">Rusak berat</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-select" required>
                                    <option value="Tersedia" selected>Tersedia</option>
                                    <option value="Rusak">Rusak</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Keterangan</label>
                                <input type="text" name="keterangan" class="form-control" 
                                    placeholder="Opsional...">
                            </div>
                        </div>

                        <div class="alert alert-warning" id="previewAlert" style="display: none;">
                            <strong>Preview:</strong> Akan ditambahkan <span id="previewJumlah">0</span> unit dengan kode:
                            <div class="mt-2" id="previewKode"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Simpan Semua Unit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Delete --}}
    <x-modal-delete />

    {{-- JavaScript untuk preview kode --}}
    <script>
        document.getElementById('jumlahUnit')?.addEventListener('input', function() {
            const jumlah = parseInt(this.value) || 0;
            const prefix = '{{ $barang->kode_barang }}';
            const previewAlert = document.getElementById('previewAlert');
            const previewJumlah = document.getElementById('previewJumlah');
            const previewKode = document.getElementById('previewKode');

            if (jumlah > 0 && jumlah <= 500) {
                previewAlert.style.display = 'block';
                previewJumlah.textContent = jumlah;

                let kodePreview = '';
                const showMax = Math.min(jumlah, 5); // Tampilkan max 5 contoh
                
                for (let i = 1; i <= showMax; i++) {
                    const num = String(i).padStart(3, '0');
                    kodePreview += `<span class="badge bg-secondary me-1">${prefix}-${num}</span>`;
                }
                
                if (jumlah > 5) {
                    kodePreview += ` <span class="text-muted">... dan ${jumlah - 5} unit lainnya</span>`;
                }
                
                previewKode.innerHTML = kodePreview;
            } else {
                previewAlert.style.display = 'none';
            }
        });
    </script>
</x-main-layout>
<x-table-list>
    <x-slot name="header">
        <tr>
            <th>#</th>
            <th>Kode</th>
            <th>Nama Barang</th>
            <th>Kategori</th>
            <th>Lokasi</th>
            <th>Kondisi</th>
            <th>Jumlah</th>
            <th>Tipe</th>
            <th>&nbsp;</th>
        </tr>
    </x-slot>

    @forelse ($barangs as $index => $barang)
        <tr>
            <td>{{ $barangs->firstItem() + $index }}</td>
            <td>{{ $barang->kode_barang }}</td>
            <td>{{ $barang->nama_barang }}</td>
            <td>{{ $barang->kategori->nama_kategori }}</td>
            <td>{{ $barang->lokasi->nama_lokasi }}</td>
            <td>
                @if ($barang->is_per_unit)
                    {{-- Barang per unit: tidak tampilkan kondisi --}}
                    <span class="text-muted">-</span>
                @else
                    {{-- Barang satuan: tampilkan kondisi --}}
                    @php
                        $badgeClass = 'bg-success';
                        if ($barang->kondisi == 'Rusak ringan') {
                            $badgeClass = 'bg-warning text-dark';
                        }
                        if ($barang->kondisi == 'Rusak berat') {
                            $badgeClass = 'bg-danger';
                        }
                    @endphp
                    <span class="badge {{ $badgeClass }}">{{ $barang->kondisi }}</span>
                @endif
            </td>
            <td>
                @if ($barang->is_per_unit)
                    {{ $barang->detailBarangs->count() }} {{ $barang->satuan }}
                @else
                    {{ $barang->jumlah }} {{ $barang->satuan }}
                @endif
            </td>
            <td>
                @if ($barang->is_per_unit)
                    @php
                        $totalUnit = $barang->detailBarangs->count();
                        $unitTersedia = $barang->detailBarangs->where('status', 'Tersedia')->count();
                        $unitDipinjam = $barang->detailBarangs->where('status', 'Dipinjam')->count();
                    @endphp
                    
                    @if ($totalUnit > 0)
                        <button class="btn btn-sm btn-outline-info" type="button" 
                            data-bs-toggle="collapse" data-bs-target="#units{{ $barang->id }}">
                            <i class="bi bi-list-ul"></i> {{ $totalUnit }} Unit
                        </button>
                    @else
                        <span class="badge bg-secondary">Per Unit</span>
                        <br><small class="text-muted">Belum ada unit</small>
                    @endif
                @else
                    <span class="badge bg-primary">Satuan</span>
                @endif
                
                {{-- Badge Tidak Dapat Dipinjam --}}
                @if (!$barang->dapat_dipinjam)
                    <br><span class="badge bg-danger mt-1">
                        <i class="bi bi-lock-fill"></i> Tidak Dapat Dipinjam
                    </span>
                @endif
            </td>
            <td class="text-end">
                @can('manage barang')
                    @if ($barang->is_per_unit)
                        <a href="{{ route('barang.units.index', $barang->id) }}" 
                            class="btn btn-sm btn-primary" title="Kelola Unit">
                            <i class="bi bi-box-seam"></i>
                        </a>
                    @endif
                    <x-tombol-aksi href="{{ route('barang.show', $barang->id) }}" type="show" />
                    <x-tombol-aksi href="{{ route('barang.edit', $barang->id) }}" type="edit" />
                @endcan

                @can('delete barang')
                    <x-tombol-aksi href="{{ route('barang.destroy', $barang->id) }}" type="delete" />
                @endcan
            </td>
        </tr>

        {{-- Collapse Row untuk Detail Unit (hanya untuk barang per unit) --}}
        @if ($barang->is_per_unit && $totalUnit > 0)
            <tr class="collapse" id="units{{ $barang->id }}">
                <td colspan="9" class="bg-light p-0">
                    <div class="p-3">
                        <h6 class="mb-3">
                            <i class="bi bi-box-seam"></i> Detail Unit - {{ $barang->nama_barang }}
                            <span class="badge bg-success ms-2">{{ $unitTersedia }} Tersedia</span>
                            <span class="badge bg-warning text-dark">{{ $unitDipinjam }} Dipinjam</span>
                        </h6>
                        
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="table-secondary">
                                    <tr>
                                        <th width="5%">No</th>
                                        <th width="15%">Kode Unit</th>
                                        <th width="12%">Kondisi</th>
                                        <th width="12%">Status</th>
                                        <th width="25%">Keterangan</th>
                                        <th width="31%">Info Peminjaman</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($barang->detailBarangs as $idx => $detail)
                                        <tr>
                                            <td>{{ $idx + 1 }}</td>
                                            <td>
                                                <strong class="text-primary">{{ $detail->sub_kode }}</strong>
                                            </td>
                                            <td>
                                                @php
                                                    $kondisiBadge = 'bg-success';
                                                    if ($detail->kondisi == 'Rusak ringan') {
                                                        $kondisiBadge = 'bg-warning text-dark';
                                                    }
                                                    if ($detail->kondisi == 'Rusak berat') {
                                                        $kondisiBadge = 'bg-danger';
                                                    }
                                                @endphp
                                                <span class="badge {{ $kondisiBadge }}">{{ $detail->kondisi }}</span>
                                            </td>
                                            <td>
                                                @if ($detail->status === 'Tersedia')
                                                    <span class="badge bg-success">
                                                        <i class="bi bi-check-circle"></i> Tersedia
                                                    </span>
                                                @elseif ($detail->status === 'Dipinjam')
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
                                                <small class="text-muted">
                                                    {{ $detail->keterangan ?? '-' }}
                                                </small>
                                            </td>
                                            <td>
                                                @php
                                                    $peminjamanAktif = $detail->peminjamanAktif;
                                                @endphp
                                                
                                                @if ($peminjamanAktif)
                                                    <small>
                                                        <i class="bi bi-person-fill text-warning"></i> 
                                                        <strong>{{ $peminjamanAktif->nama_peminjam }}</strong><br>
                                                        <i class="bi bi-calendar-event"></i> 
                                                        Sejak: {{ $peminjamanAktif->tanggal_pinjam->format('d/m/Y') }}
                                                        @if ($peminjamanAktif->kontak)
                                                            <br><i class="bi bi-telephone"></i> {{ $peminjamanAktif->kontak }}
                                                        @endif
                                                    </small>
                                                @else
                                                    <small class="text-muted">-</small>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </td>
            </tr>
        @endif
    @empty
        <tr>
            <td colspan="9" class="text-center">
                <div class="alert alert-danger">
                    Data barang belum tersedia.
                </div>
            </td>
        </tr>
    @endforelse
</x-table-list>
<x-main-layout title-page="Tambah Peminjaman Barang">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Form Peminjaman Barang</h5>
                </div>

                <div class="card-body">
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('peminjaman.store') }}">
                        @csrf

                        {{-- Pilih Barang & Unit --}}
                        <div class="mb-3">
                            <label for="detail_barang_id" class="form-label">
                                Pilih Barang & Unit <span class="text-danger">*</span>
                            </label>
                            <select name="detail_barang_id" id="detail_barang_id" 
                                class="form-select @error('detail_barang_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Barang --</option>
                                @foreach ($barangs as $barang)
                                    <optgroup label="{{ $barang->nama_barang }}">
                                        @foreach ($barang->detailBarangs as $detail)
                                            <option value="{{ $detail->id }}" 
                                                {{ old('detail_barang_id') == $detail->id ? 'selected' : '' }}>
                                                {{ $detail->sub_kode }} - {{ $detail->kondisi }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            @error('detail_barang_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Pilih unit barang yang akan dipinjam</small>
                        </div>

                        {{-- Nama Peminjam --}}
                        <div class="mb-3">
                            <label for="nama_peminjam" class="form-label">
                                Nama Peminjam <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="nama_peminjam" id="nama_peminjam" 
                                class="form-control @error('nama_peminjam') is-invalid @enderror" 
                                value="{{ old('nama_peminjam') }}" required>
                            @error('nama_peminjam')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Kontak --}}
                        <div class="mb-3">
                            <label for="kontak" class="form-label">Kontak (HP/Email)</label>
                            <input type="text" name="kontak" id="kontak" 
                                class="form-control @error('kontak') is-invalid @enderror" 
                                value="{{ old('kontak') }}" 
                                placeholder="08xxxxxxxxxx / email@example.com">
                            @error('kontak')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Keperluan --}}
                        <div class="mb-3">
                            <label for="keperluan" class="form-label">Keperluan</label>
                            <textarea name="keperluan" id="keperluan" rows="3" 
                                class="form-control @error('keperluan') is-invalid @enderror" 
                                placeholder="Jelaskan keperluan peminjaman...">{{ old('keperluan') }}</textarea>
                            @error('keperluan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Tanggal Pinjam --}}
                        <div class="mb-3">
                            <label for="tanggal_pinjam" class="form-label">
                                Tanggal Pinjam <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="tanggal_pinjam" id="tanggal_pinjam" 
                                class="form-control @error('tanggal_pinjam') is-invalid @enderror" 
                                value="{{ old('tanggal_pinjam', date('Y-m-d')) }}" required>
                            @error('tanggal_pinjam')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Catatan --}}
                        <div class="mb-3">
                            <label for="catatan" class="form-label">Catatan</label>
                            <textarea name="catatan" id="catatan" rows="2" 
                                class="form-control @error('catatan') is-invalid @enderror" 
                                placeholder="Catatan tambahan...">{{ old('catatan') }}</textarea>
                            @error('catatan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Buttons --}}
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('peminjaman.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Simpan Peminjaman
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Info Card --}}
            <div class="card mt-3">
                <div class="card-body bg-light">
                    <h6 class="mb-2"><i class="bi bi-info-circle"></i> Informasi</h6>
                    <ul class="mb-0 small">
                        <li>Pilih barang dan unit yang akan dipinjam dari dropdown</li>
                        <li>Pastikan data peminjam lengkap dan valid</li>
                        <li>Status barang otomatis berubah menjadi "Dipinjam"</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-main-layout>
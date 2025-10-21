@csrf
<div class="row mb-3">
    <div class="col-md-6">
        <x-form-input label="Kode Barang" name="kode_barang" :value="$barang->kode_barang" />
    </div>

    <div class="col-md-6">
        <x-form-input label="Nama Barang" name="nama_barang" :value="$barang->nama_barang" />
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <x-form-select label="Kategori" name="kategori_id" :value="$barang->kategori_id"
            :option-data="$kategori" option-label="nama_kategori" option-value="id" />
    </div>

    <div class="col-md-6">
        <x-form-select label="Lokasi" name="lokasi_id" :value="$barang->lokasi_id"
            :option-data="$lokasi" option-label="nama_lokasi" option-value="id" />
    </div>
</div>

{{-- Checkbox Barang Per Unit & Dapat Dipinjam --}}
<div class="row mb-3">
    <div class="col-md-6">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_per_unit" id="isPerUnit" 
                value="1" {{ old('is_per_unit', $barang->is_per_unit ?? false) ? 'checked' : '' }}
                onchange="toggleJumlahField()">
            <label class="form-check-label" for="isPerUnit">
                <strong>Barang Per Unit</strong>
                <small class="text-muted d-block">
                    Centang jika barang ini dikelola per unit (contoh: Laptop, AC, Kursi). 
                    Jumlah akan otomatis dihitung dari unit yang ditambahkan melalui "Kelola Unit".
                </small>
            </label>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="dapat_dipinjam" id="dapatDipinjam" 
                value="1" 
                @if(isset($update))
                    {{ old('dapat_dipinjam', $barang->dapat_dipinjam) ? 'checked' : '' }}
                @else
                    {{ old('dapat_dipinjam', true) ? 'checked' : '' }}
                @endif
            >
            <label class="form-check-label" for="dapatDipinjam">
                <strong>Dapat Dipinjam</strong>
                <small class="text-muted d-block">
                    Centang jika barang ini dapat dipinjam. 
                    Jangan centang untuk barang permanen (contoh: AC terpasang, Whiteboard di tembok, Meja Lab).
                </small>
            </label>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6" id="jumlahField">
        <x-form-input label="Jumlah" name="jumlah" :value="$barang->jumlah" type="number" />
        <small class="text-muted" id="jumlahHint">
            Masukkan jumlah barang (untuk barang satuan biasa)
        </small>
    </div>

    <div class="col-md-6">
        @php
            $satuanOptions = [
                ['satuan' => 'Unit'],
                ['satuan' => 'Buah'], 
                ['satuan' => 'Pcs'],
                ['satuan' => 'Set'],
                ['satuan' => 'Box'],
                ['satuan' => 'Dus'],
                ['satuan' => 'Pack'],
                ['satuan' => 'Sachet'],
                ['satuan' => 'Botol'],
                ['satuan' => 'Tablet'],
                ['satuan' => 'Kapsul'],
                ['satuan' => 'Lembar'],
                ['satuan' => 'Roll'],
                ['satuan' => 'Meter'],
                ['satuan' => 'Kg'],
                ['satuan' => 'Liter'],
            ];
        @endphp

        <x-form-select label="Satuan" name="satuan" :value="$barang->satuan" 
            :option-data="$satuanOptions" option-label="satuan" option-value="satuan" />
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        @php
            $kondisi = [
                ['kondisi' => 'Baik'], 
                ['kondisi' => 'Rusak ringan'], 
                ['kondisi' => 'Rusak berat']
            ];
        @endphp

        <x-form-select label="Kondisi" name="kondisi" :value="$barang->kondisi" :option-data="$kondisi"
            option-label="kondisi" option-value="kondisi" />
    </div>

    <div class="col-md-6">
        @php
            $sumberDana = [
                ['sumber_dana' => 'Pemerintah'], 
                ['sumber_dana' => 'Swadaya'], 
                ['sumber_dana' => 'Donatur']
            ];
        @endphp

        <x-form-select label="Sumber Dana" name="sumber_dana" :value="$barang->sumber_dana" 
            :option-data="$sumberDana" option-label="sumber_dana" option-value="sumber_dana" />
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        @php
            $tanggal = $barang->tanggal_pengadaan
                ? date('Y-m-d', strtotime($barang->tanggal_pengadaan))
                : null;
        @endphp
        <x-form-input label="Tanggal Pengadaan" name="tanggal_pengadaan" type="date" :value="$tanggal" />
    </div>

    <div class="col-md-6">
        <x-form-input label="Gambar Barang" name="gambar" type="file" />
    </div>
</div>

{{-- Info untuk tambah unit setelah barang dibuat --}}
@if (!isset($update))
    <div class="alert alert-info" id="infoPerUnit" style="display: none;">
        <i class="bi bi-info-circle"></i> 
        <strong>Info:</strong> Setelah barang berhasil disimpan, Anda akan diarahkan ke halaman <strong>"Kelola Unit"</strong> untuk menambahkan detail unit barang.
    </div>
@endif

<div class="mt-4">
    <x-primary-button>
        {{ isset($update) ? __('Update') : __('Simpan') }}
    </x-primary-button>
    
    <x-tombol-kembali :href="route('barang.index')" />
</div>

{{-- JavaScript untuk toggle field jumlah --}}
<script>
function toggleJumlahField() {
    const checkbox = document.getElementById('isPerUnit');
    const jumlahField = document.getElementById('jumlahField');
    const jumlahInput = jumlahField.querySelector('input[name="jumlah"]');
    const jumlahHint = document.getElementById('jumlahHint');
    const infoPerUnit = document.getElementById('infoPerUnit');
    
    if (checkbox.checked) {
        // Barang per unit
        jumlahInput.value = '0';
        jumlahInput.disabled = true;
        jumlahInput.style.backgroundColor = '#e9ecef';
        jumlahHint.textContent = 'Jumlah akan otomatis dihitung dari unit yang ditambahkan';
        jumlahHint.style.color = '#6c757d';
        
        if (infoPerUnit) {
            infoPerUnit.style.display = 'block';
        }
    } else {
        // Barang satuan biasa
        jumlahInput.disabled = false;
        jumlahInput.style.backgroundColor = '';
        jumlahHint.textContent = 'Masukkan jumlah barang (untuk barang satuan biasa)';
        jumlahHint.style.color = '#6c757d';
        
        if (infoPerUnit) {
            infoPerUnit.style.display = 'none';
        }
    }
}

// Jalankan saat halaman load
document.addEventListener('DOMContentLoaded', function() {
    toggleJumlahField();
});
</script>
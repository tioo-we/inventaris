<div class="row">
    <div class="col">
        @can('manage lokasi')
        <x-tombol-tambah label="Tambah Lokasi" href="{{ route('lokasi.create') }}" />
        @endcan
       </div>
    <div class="col">
        <x-form-search placeholder="Cari nama Lokasi..." />
    </div>
</div>
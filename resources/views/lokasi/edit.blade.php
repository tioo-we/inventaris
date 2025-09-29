<x-main-layout :title-page="__('Edit Lokasi')">
    <div class="row">
        <form class="card col-lg-6" action="{{ route('lokasi.update', $lokasi->id) }}" method="POST">
            <div class="card-body">
                @method('PUT')
                @include('lokasi.partials._form', ['update' => true])
            </div>
        </form>
    </div>
</x-main-layout>
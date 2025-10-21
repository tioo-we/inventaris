<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Barang extends Model
{
    protected $guarded = ['id'];
    
    protected $casts = [
        'tanggal_pengadaan' => 'date',
        'is_per_unit' => 'boolean',
        'dapat_dipinjam' => 'boolean',
    ];

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }

    public function lokasi(): BelongsTo
    {
        return $this->belongsTo(Lokasi::class, 'lokasi_id');
    }

    public function detailBarangs(): HasMany
    {
        return $this->hasMany(DetailBarang::class, 'barang_id');
    }

    /**
     * Mendapatkan jumlah barang
     * - Jika per unit: hitung dari detail_barangs
     * - Jika bukan per unit: ambil dari field jumlah
     */
    public function getJumlahActualAttribute()
    {
        if ($this->is_per_unit) {
            return $this->detailBarangs->count();
        }
        
        return $this->jumlah;
    }
}
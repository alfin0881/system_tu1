<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Surat extends Model
{
    protected $fillable = [
        'jenis_surat_id',
        'nomor_surat',
        'nomor_urut',
        'tanggal_surat',
        'perihal',
        'data_isian',
        'file_path',
        'status', // draft | final
        'created_by',
    ];

    protected $casts = [
        'tanggal_surat' => 'date',
        'data_isian' => 'array',
    ];

    public function jenisSurat()
    {
        return $this->belongsTo(JenisSurat::class);
    }

    public function dibuatOleh()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeFinal($query)
    {
        return $query->where('status', 'final');
    }
}

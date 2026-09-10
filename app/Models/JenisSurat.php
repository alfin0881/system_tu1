<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisSurat extends Model
{
    protected $fillable = [
        'nama',
        'kode',
        'kategori',
        'format_nomor',
        'template_path',
        'template_nama_asli',
        'template_variables',
        'deskripsi',
        'aktif',
    ];

    protected $casts = [
        'aktif' => 'boolean',
        'template_variables' => 'array',
    ];

    public function surat()
    {
        return $this->hasMany(Surat::class);
    }

    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }

    public function hasTemplate(): bool
    {
        return ! empty($this->template_path);
    }
}

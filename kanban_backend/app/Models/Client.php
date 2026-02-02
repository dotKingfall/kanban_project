<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'email',
        'whatsapp',
        'avisar_por_email',
        'avisar_por_whatsapp',
        'observacao',
        'reverse_filter',
        'global_filter_id',
    ];

    protected $casts = [
        'avisar_por_email' => 'boolean',
        'avisar_por_whatsapp' => 'boolean',
        'reverse_filter' => 'boolean',
    ];
}

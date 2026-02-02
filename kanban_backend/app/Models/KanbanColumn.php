<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KanbanColumn extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'name',
        'position',
        'is_fixed',
        'is_hidden',
        'reverse_filter',
    ];

    protected $casts = [
        'is_fixed' => 'boolean',
        'is_hidden' => 'boolean',
        'reverse_filter' => 'boolean',
    ];
}

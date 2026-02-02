<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Demand extends Model
{
    use HasFactory;

    protected $fillable = [
        'cliente',
        'kanban_column_id',
        'position_in_column',
        'titulo',
        'descricao_detalhada',
        'responsavel',
        'quem_deve_testar',
        'anexos',
        'cobrada_do_cliente',
        'flag_returned',
        'tempo_estimado',
        'tempo_gasto',
        'data_cadastro',
        'prioridade',
        'setor',
        'status',
        'priority_table_id',
        'department_table_id',
    ];

    protected $casts = [
        'anexos' => 'array',
        'cobrada_do_cliente' => 'boolean',
        'flag_returned' => 'boolean',
        'data_cadastro' => 'datetime',
    ];

    // Relationships
    public function client()
    {
        return $this->belongsTo(Client::class, 'cliente');
    }
}

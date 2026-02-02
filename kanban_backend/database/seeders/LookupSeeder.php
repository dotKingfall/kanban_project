<?php

namespace Database\Seeders;

use App\Models\ClientFilterType;
use App\Models\DemandFilterType;
use App\Models\Department;
use App\Models\Priority;
use Illuminate\Database\Seeder;

class LookupSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Client Filter Types
        $clientFilters = ['Nome', 'Email', 'Data'];
        foreach ($clientFilters as $name) {
            ClientFilterType::create(['name' => $name]);
        }

        // 2. Demand Filter Types
        $demandFilters = [
            'Título', 'Prioridade', 'Setor', 'Responsável',
            'Tempo estimado', 'Tempo Gasto', 'Status', 'Retorno', 'Data'
        ];
        foreach ($demandFilters as $name) {
            DemandFilterType::create(['name' => $name]);
        }

        // 3. Priorities
        $priorities = [
            ['name' => 'Urgente', 'value' => 25],
            ['name' => 'Alta', 'value' => 50],
            ['name' => 'Média', 'value' => 75],
            ['name' => 'Baixa', 'value' => 100],
        ];
        foreach ($priorities as $p) {
            Priority::create($p);
        }

        // 4. Departments
        $departments = [
            'Financeiro', 'Executivo', 'RH', 'TI', 'Jurídico', 'Comercial'
        ];
        foreach ($departments as $name) {
            Department::create(['name' => $name]);
        }
    }
}

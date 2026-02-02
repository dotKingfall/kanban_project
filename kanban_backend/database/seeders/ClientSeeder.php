<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Demand;
use App\Models\Department;
use App\Models\KanbanColumn;
use App\Models\Priority;
use App\Models\User;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $priorities = Priority::all();
        $departments = Department::all();
        $users = User::all();

        // Create 40 clients (20 for each of the 2 users)
        for ($i = 0; $i < 40; $i++) {
            $client = Client::create([
                'nome' => fake()->company(),
                'email' => fake()->companyEmail(),
                'whatsapp' => fake()->phoneNumber(),
                'observacao' => rand(0, 1) ? fake()->paragraph() : null,
                'avisar_por_email' => rand(0, 1),
                'avisar_por_whatsapp' => rand(0, 1),
                'user_id' => $i < 20 ? $users[0]->id : $users[1]->id,
            ]);

            // Create Kanban Columns for this client
            $columnNames = [
                'Backlog', 'Autorização', 'Fila', 'Em desenvolvimento',
                'Teste', 'Deploy', 'Concluído'
            ];
            
            $createdColumns = [];

            foreach ($columnNames as $index => $name) {
                $createdColumns[] = KanbanColumn::create([
                    'client_id' => $client->id,
                    'name' => $name,
                    'position' => $index,
                    'is_fixed' => false,
                ]);
            }

            // Initialize position counters for each column to avoid collisions
            $columnPositions = [];
            foreach ($createdColumns as $col) {
                $columnPositions[$col->id] = 0;
            }

            // Create 50 Demands for this client
            for ($j = 0; $j < 50; $j++) {
                // Pick random relations
                $column = $createdColumns[array_rand($createdColumns)];
                $priority = $priorities->random();
                $department = $departments->random();

                // Generate 1 to 6 random names for testers
                $testerCount = rand(1, 6);
                $testers = [];
                for ($t = 0; $t < $testerCount; $t++) {
                    $testers[] = fake()->firstName();
                }

                Demand::create([
                    'cliente' => $client->id,
                    'kanban_column_id' => $column->id,
                    'priority_table_id' => $priority->id,
                    'department_table_id' => $department->id,
                    
                    // Snapshot strings
                    'prioridade' => $priority->name,
                    'setor' => $department->name,
                    'status' => $column->name,

                    // Fields
                    'titulo' => fake()->sentence(4),
                    'descricao_detalhada' => fake()->paragraph(),
                    'responsavel' => fake()->name(),
                    'quem_deve_testar' => implode(', ', $testers),
                    'tempo_estimado' => rand(1, 100),
                    'tempo_gasto' => rand(0, 100),
                    'position_in_column' => $columnPositions[$column->id]++,
                    'cobrada_do_cliente' => (bool)rand(0, 1),
                    'flag_returned' => (bool)rand(0, 1),
                ]);
            }
        }
    }
}

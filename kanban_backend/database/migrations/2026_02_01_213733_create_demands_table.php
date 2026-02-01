<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('demands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente')->constrained('clients')->onDelete('cascade');
            $table->integer('position_in_column')->default(0);

            //MVP fields
            $table->string('titulo');
            $table->text('descricao_detalhada')->nullable();
            $table->string('responsavel')->nullable();
            $table->string('quem_deve_testar')->nullable();
            $table->json('anexos')->nullable();
            $table->boolean('cobrada_do_cliente')->default(false);
            $table->boolean('flag_returned')->default(false);
            $table->float('tempo_estimado')->default(0);
            $table->float('tempo_gasto')->default(0);
            $table->timestamp('data_cadastro')->useCurrent();
            $table->string('prioridade')->nullable();
            $table->string('setor')->nullable();
            $table->string('status')->nullable();

            //Table lookup fields
            $table->foreignId('priority_table_id')->nullable()->constrained('priorities')->nullOnDelete();
            $table->foreignId('department_table_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('kanban_column_id')->constrained('kanban_columns')->nullOnDelete();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('demands');
    }
};

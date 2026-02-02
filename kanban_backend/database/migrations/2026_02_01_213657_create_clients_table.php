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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('email')->nullable();
            $table->string('whatsapp')->nullable();
            $table->boolean('avisar_por_email')->default(false);
            $table->boolean('avisar_por_whatsapp')->default(false);
            $table->text('observacao')->nullable();
            $table->boolean('reverse_filter')->default(false);
        
            // Foreign keys to demand filter types and user id
            $table->foreignId('global_filter_id')->nullable()->constrained('demand_filter_types')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};

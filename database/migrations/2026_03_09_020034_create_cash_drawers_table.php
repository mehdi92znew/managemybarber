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
        Schema::create('cash_drawers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->decimal('starting_cash', 10, 2)->default(0);
            $table->timestamp('closed_at')->nullable();
            $table->decimal('closing_cash', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // A shop can only have one cash drawer record per day
            $table->unique(['shop_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_drawers');
    }
};

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
        Schema::table('services', function (Blueprint $table) {
            $table->boolean('has_special_commission')->default(false)->after('is_extra');
            $table->enum('commission_type', ['percentage', 'fixed'])->default('percentage')->after('has_special_commission');
            $table->decimal('commission_value', 10, 2)->nullable()->after('commission_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['has_special_commission', 'commission_type', 'commission_value']);
        });
    }
};

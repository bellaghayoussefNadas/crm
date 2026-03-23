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
        $tables = ['users', 'clients', 'leads', 'tasks', 'activities'];
        foreach ($tables as $t) {
            Schema::table($t, function (Blueprint $table) {
                $table->foreignId('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['users', 'clients', 'leads', 'tasks', 'activities'];
        foreach ($tables as $t) {
            Schema::table($t, function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
                $table->dropColumn('tenant_id');
            });
        }
    }
};

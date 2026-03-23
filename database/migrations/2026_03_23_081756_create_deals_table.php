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
        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('contact_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name');
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('currency', 3)->default('EUR');
            $table->string('stage')->default('lead');
            $table->integer('probability')->default(10);
            $table->date('expected_close_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deals');
    }
};

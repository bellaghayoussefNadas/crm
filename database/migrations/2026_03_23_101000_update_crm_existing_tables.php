<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Fix for missing lead fields
        Schema::table('leads', function (Blueprint $table) {
            if (!Schema::hasColumn('leads', 'source')) $table->string('source')->nullable();
            if (!Schema::hasColumn('leads', 'score'))  $table->integer('score')->default(0);
            if (!Schema::hasColumn('leads', 'last_contact_at')) $table->timestamp('last_contact_at')->nullable();
        });

        // Fix for missing contact fields
        Schema::table('contacts', function (Blueprint $table) {
            if (!Schema::hasColumn('contacts', 'source')) $table->string('source')->nullable();
            if (!Schema::hasColumn('contacts', 'last_contact_at')) $table->timestamp('last_contact_at')->nullable();
        });

        // Taggables table for segmentation
        if (!Schema::hasTable('taggables')) {
            Schema::create('taggables', function (Blueprint $table) {
                $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
                $table->morphs('taggable');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('taggables');
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn(['source', 'score', 'last_contact_at']);
        });
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn(['source', 'last_contact_at']);
        });
    }
};

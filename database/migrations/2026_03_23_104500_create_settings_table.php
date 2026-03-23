<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }

        // Initialize default settings if not already seeded
        $defaults = [
            ['key' => 'site_name', 'value' => 'GoTravel CRM'],
            ['key' => 'currency',  'value' => 'TND'],
            ['key' => 'locale',    'value' => 'fr'],
            ['key' => 'logo_url',  'value' => ''],
        ];

        foreach ($defaults as $default) {
            if (!DB::table('settings')->where('key', $default['key'])->exists()) {
                DB::table('settings')->insert(array_merge($default, ['created_at' => now(), 'updated_at' => now()]));
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};

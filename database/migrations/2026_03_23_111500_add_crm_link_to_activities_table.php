<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->foreignId('contact_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('account_id')->nullable()->constrained()->nullOnDelete();
            $table->string('subject')->nullable();
            $table->string('message_id')->nullable()->unique(); // For IMAP tracking
        });
    }

    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropForeign(['contact_id']);
            $table->dropForeign(['account_id']);
            $table->dropColumn(['contact_id', 'account_id', 'subject', 'message_id']);
        });
    }
};

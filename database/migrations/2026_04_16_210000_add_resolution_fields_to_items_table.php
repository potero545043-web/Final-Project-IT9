<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table): void {
            $table->dateTime('resolved_at')->nullable()->after('status');
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete()->after('resolved_at');
            $table->enum('resolution_type', [
                'returned_to_owner',
                'unclaimed_closed',
                'invalid_report',
                'other',
            ])->nullable()->after('resolved_by');
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table): void {
            $table->dropColumn(['resolved_at', 'resolved_by', 'resolution_type']);
        });
    }
};

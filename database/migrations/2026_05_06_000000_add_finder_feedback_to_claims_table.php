<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('claims', function (Blueprint $table): void {
            $table->string('finder_feedback', 30)->nullable()->after('review_notes');
            $table->text('finder_notes')->nullable()->after('finder_feedback');
        });
    }

    public function down(): void
    {
        Schema::table('claims', function (Blueprint $table): void {
            $table->dropColumn(['finder_feedback', 'finder_notes']);
        });
    }
};

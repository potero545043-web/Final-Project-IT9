<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('claims', function (Blueprint $table): void {
            $table->string('contact_name')->nullable()->after('proof_image_url');
            $table->string('contact_email')->nullable()->after('contact_name');
            $table->string('contact_phone', 30)->nullable()->after('contact_email');
        });
    }

    public function down(): void
    {
        Schema::table('claims', function (Blueprint $table): void {
            $table->dropColumn(['contact_name', 'contact_email', 'contact_phone']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('claims', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('claimant_id')->constrained('users')->cascadeOnDelete();
            $table->text('message');
            $table->text('proof_details');
            $table->string('status', 30)->default('pending');
            $table->text('review_notes')->nullable();
            $table->timestamps();

            $table->unique(['item_id', 'claimant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('claims');
    }
};

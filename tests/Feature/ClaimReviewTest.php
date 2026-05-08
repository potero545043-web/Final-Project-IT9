<?php

namespace Tests\Feature;

use App\Models\Claim;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClaimReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_item_reporter_can_record_finder_feedback_without_approving_claim(): void
    {
        [$reporter, $claimant, $claim] = $this->createPendingClaim();

        $response = $this
            ->actingAs($reporter)
            ->patch(route('claims.update', $claim), [
                'finder_feedback' => 'confirmed',
                'finder_notes' => 'The proof details match the item I found.',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $claim->refresh();

        $this->assertSame('pending', $claim->status);
        $this->assertSame('confirmed', $claim->finder_feedback);
        $this->assertSame('The proof details match the item I found.', $claim->finder_notes);
        $this->assertSame('under_review', $claim->item->fresh()->status);
        $this->assertSame('student', $claimant->role);
    }

    public function test_admin_can_make_final_claim_decision(): void
    {
        [, , $claim] = $this->createPendingClaim();
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this
            ->actingAs($admin)
            ->patch(route('claims.update', $claim), [
                'status' => 'approved',
                'review_notes' => 'Proof verified by admin.',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $claim->refresh();

        $this->assertSame('approved', $claim->status);
        $this->assertSame('Proof verified by admin.', $claim->review_notes);
        $this->assertSame('claimed', $claim->item->fresh()->status);
    }

    /**
     * @return array{0: User, 1: User, 2: Claim}
     */
    private function createPendingClaim(): array
    {
        $reporter = User::factory()->create(['role' => 'student']);
        $claimant = User::factory()->create(['role' => 'student']);

        $item = Item::create([
            'user_id' => $reporter->id,
            'type' => 'found',
            'category' => 'id',
            'title' => 'Driver License',
            'description' => 'Found near the lobby.',
            'location' => 'Main Lobby',
            'reported_at' => now(),
            'status' => 'under_review',
            'contact_name' => $reporter->name,
            'contact_email' => $reporter->email,
        ]);

        $claim = Claim::create([
            'item_id' => $item->id,
            'claimant_id' => $claimant->id,
            'message' => 'That license belongs to me.',
            'proof_details' => 'Name and photo match my ID.',
            'status' => 'pending',
        ]);

        return [$reporter, $claimant, $claim];
    }
}

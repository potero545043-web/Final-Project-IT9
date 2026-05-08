<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Claim extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'claimant_id',
        'message',
        'proof_details',
        'proof_image_url',
        'contact_name',
        'contact_email',
        'contact_phone',
        'status',
        'review_notes',
        'finder_feedback',
        'finder_notes',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function claimant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'claimant_id');
    }

    public function getProofImageSrcAttribute(): ?string
    {
        if (! $this->proof_image_url) {
            return null;
        }

        if (Str::startsWith($this->proof_image_url, ['http://', 'https://'])) {
            $path = Str::after(parse_url($this->proof_image_url, PHP_URL_PATH) ?? '', '/storage/');

            return $path !== ''
                ? asset('storage/'.ltrim($path, '/'))
                : $this->proof_image_url;
        }

        return asset('storage/'.ltrim($this->proof_image_url, '/'));
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Claim Submitted',
            'approved' => 'Claim Approved',
            'rejected' => 'Claim Rejected',
            default => str($this->status)->headline(),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'status-chip-attention',
            'approved' => 'status-chip-success',
            'rejected' => 'status-chip-danger',
            default => 'chip-neutral',
        };
    }

    public function getFinderFeedbackLabelAttribute(): ?string
    {
        return match ($this->finder_feedback) {
            'confirmed' => 'Finder Confirmed Match',
            'doubted' => 'Finder Doubts Claim',
            default => null,
        };
    }

    public function getFinderFeedbackBadgeClassAttribute(): string
    {
        return match ($this->finder_feedback) {
            'confirmed' => 'status-chip-success',
            'doubted' => 'status-chip-danger',
            default => 'chip-neutral',
        };
    }
}

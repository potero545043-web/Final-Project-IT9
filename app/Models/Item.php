<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'category',
        'title',
        'slug',
        'description',
        'location',
        'reported_at',
        'status',
        'resolved_at',
        'resolved_by',
        'resolution_type',
        'deleted_at',
        'deleted_by',
        'archived_from_status',
        'contact_name',
        'contact_email',
        'contact_phone',
        'photo_url',
        'reward_amount',
    ];

    protected function casts(): array
    {
        return [
            'reported_at' => 'datetime',
            'resolved_at' => 'datetime',
            'deleted_at' => 'datetime',
            'reward_amount' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Item $item): void {
            $item->slug = static::uniqueSlug($item->title);
        });

        static::updating(function (Item $item): void {
            if ($item->isDirty('title') && ! $item->isDirty('slug')) {
                $item->slug = static::uniqueSlug($item->title, $item->id);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function claims(): HasMany
    {
        return $this->hasMany(Claim::class);
    }

    public function approvedClaim(): HasOne
    {
        return $this->hasOne(Claim::class)
            ->where('status', 'approved')
            ->latestOfMany();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['type'] ?? null, fn (Builder $builder, string $type) => $builder->where('type', $type))
            ->when($filters['status'] ?? null, fn (Builder $builder, string $status) => $builder->where('status', $status))
            ->when($filters['category'] ?? null, fn (Builder $builder, string $category) => $builder->where('category', $category))
            ->when($filters['q'] ?? null, function (Builder $builder, string $search): void {
                $builder->where(function (Builder $nested) use ($search): void {
                    $nested
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%");
                });
            });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotIn('status', ['resolved', 'deleted']);
    }

    public function scopeResolved(Builder $query): Builder
    {
        return $query->where('status', 'resolved');
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->where('status', 'deleted');
    }

    public function getPhotoSrcAttribute(): ?string
    {
        if (! $this->photo_url) {
            return null;
        }

        if (Str::startsWith($this->photo_url, ['http://', 'https://'])) {
            $path = Str::after(parse_url($this->photo_url, PHP_URL_PATH) ?? '', '/storage/');

            return $path !== ''
                ? asset('storage/'.ltrim($path, '/'))
                : $this->photo_url;
        }

        return asset('storage/'.ltrim($this->photo_url, '/'));
    }

    public function getStatusLabelAttribute(): string
    {
        return static::statusLabelFor($this->status);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'open' => 'status-chip-info',
            'under_review' => 'status-chip-attention',
            'claimed' => 'status-chip-success',
            'resolved' => 'status-chip-success',
            'deleted' => 'status-chip-danger',
            default => 'chip-neutral',
        };
    }

    public static function statusLabelFor(string $status): string
    {
        return match ($status) {
            'open' => 'Open',
            'under_review' => 'Under Review',
            'claimed' => 'Claimed',
            'resolved' => 'Resolved',
            'deleted' => 'Archived',
            default => str($status)->headline(),
        };
    }

    public function statusTimeline(): array
    {
        $claims = $this->relationLoaded('claims') ? $this->claims : $this->claims()->with('claimant')->get();

        $events = collect([
            [
                'timestamp' => $this->reported_at ?? $this->created_at,
                'title' => 'Report submitted',
                'description' => 'The item was reported and is now visible for review.',
            ],
        ]);

        $claims->each(function (Claim $claim) use ($events): void {
            $events->push([
                'timestamp' => $claim->created_at,
                'title' => 'Claim submitted',
                'description' => $claim->claimant
                    ? "{$claim->claimant->name} submitted an ownership request."
                    : 'An ownership request was submitted.',
            ]);

            if ($claim->status === 'approved') {
                $events->push([
                    'timestamp' => $claim->updated_at,
                    'title' => 'Claim approved',
                    'description' => $claim->claimant
                        ? "The claim from {$claim->claimant->name} was approved."
                        : 'A claim was approved for this report.',
                ]);
            }
        });

        if ($this->status === 'under_review') {
            $events->push([
                'timestamp' => $this->updated_at,
                'title' => 'Under review',
                'description' => 'This report is currently being checked by the owner or administrator.',
            ]);
        }

        if ($this->status === 'resolved') {
            $events->push([
                'timestamp' => $this->updated_at,
                'title' => 'Resolved',
                'description' => 'This report has been marked as resolved.',
            ]);
        }

        if ($this->status === 'deleted') {
            $events->push([
                'timestamp' => $this->deleted_at ?? $this->updated_at,
                'title' => 'Archived',
                'description' => 'This report was removed from normal user views and kept for admin review.',
            ]);
        }

        if ($this->status === 'claimed') {
            $events->push([
                'timestamp' => $this->updated_at,
                'title' => 'Claim approved',
                'description' => 'The report has moved forward with an approved ownership claim.',
            ]);
        }

        return $events
            ->filter(fn (array $event) => ! empty($event['timestamp']))
            ->sortByDesc(fn (array $event) => $event['timestamp'])
            ->values()
            ->all();
    }

    public static function categories(): array
    {
        return [
            'wallet',
            'electronics',
            'documents',
            'school_supply',
            'bag',
            'clothing',
            'keys',
            'other',
        ];
    }

    public static function statuses(): array
    {
        return [
            'open',
            'under_review',
            'claimed',
            'resolved',
        ];
    }

    private static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'item';
        $slug = $base;
        $counter = 2;

        while (
            static::query()
                ->when($ignoreId, fn (Builder $query) => $query->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}

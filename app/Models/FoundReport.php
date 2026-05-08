<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FoundReport extends Model
{
    protected $table = 'found_reports';
    protected $primaryKey = 'found_report_id';

    protected $fillable = [
        'user_id',
        'item_id',
        'description',
        'location',
        'date_reported',
        'status',
    ];

    protected $casts = [
        'date_reported' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LostReport extends Model
{
    protected $table = 'lost_reports';
    protected $primaryKey = 'lost_report_id';

    protected $fillable = [
        'user_id',
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
}

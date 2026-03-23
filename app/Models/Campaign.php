<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'type', 'subject', 'body', 'status',
        'user_id', 'scheduled_at', 'sent_at',
        'recipients_count', 'opens_count', 'clicks_count'
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function user() { return $this->belongsTo(User::class); }
}

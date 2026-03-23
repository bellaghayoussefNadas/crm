<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject', 'description', 'contact_id', 'account_id',
        'assigned_to', 'created_by', 'status', 'priority',
        'category', 'resolution', 'resolved_at'
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function contact() { return $this->belongsTo(Contact::class); }
    public function account() { return $this->belongsTo(Account::class); }
    public function assignedTo() { return $this->belongsTo(User::class, 'assigned_to'); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by'); }
}

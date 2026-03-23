<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference', 'title', 'contact_id', 'account_id', 'deal_id', 'user_id',
        'status', 'subtotal', 'tax_rate', 'tax_amount', 'total',
        'currency', 'notes', 'valid_until', 'line_items'
    ];

    protected $casts = [
        'line_items' => 'array',
        'valid_until' => 'date',
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function contact() { return $this->belongsTo(Contact::class); }
    public function account() { return $this->belongsTo(Account::class); }
    public function deal() { return $this->belongsTo(Deal::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function invoice() { return $this->hasOne(Invoice::class); }
}

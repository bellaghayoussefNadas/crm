<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference', 'contact_id', 'account_id', 'quote_id', 'user_id',
        'status', 'subtotal', 'tax_rate', 'tax_amount', 'total', 'amount_paid',
        'currency', 'notes', 'due_date', 'paid_at', 'line_items'
    ];

    protected $casts = [
        'line_items' => 'array',
        'due_date' => 'date',
        'paid_at' => 'date',
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'amount_paid' => 'decimal:2',
    ];

    public function contact() { return $this->belongsTo(Contact::class); }
    public function account() { return $this->belongsTo(Account::class); }
    public function quote() { return $this->belongsTo(Quote::class); }
    public function user() { return $this->belongsTo(User::class); }

    public function getAmountDueAttribute(): float
    {
        return (float)$this->total - (float)$this->amount_paid;
    }
}

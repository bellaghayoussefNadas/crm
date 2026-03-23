<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToTenant;

class Contact extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'account_id', 'first_name', 'last_name', 'email', 'phone', 'job_title', 'source', 'last_contact_at'
    ];

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}

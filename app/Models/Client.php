<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'phone',
        'company',
        'address',
        'notes',
    ];

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }
}

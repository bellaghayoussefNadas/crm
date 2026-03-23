<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'type', 'parent_id', 'file_path', 'file_name', 'mime_type', 'file_size',
        'category', 'contact_id', 'account_id', 'deal_id',
        'uploaded_by', 'notes'
    ];

    public function contact() { return $this->belongsTo(Contact::class); }
    public function account() { return $this->belongsTo(Account::class); }
    public function deal() { return $this->belongsTo(Deal::class); }
    public function uploadedBy() { return $this->belongsTo(User::class, 'uploaded_by'); }
    
    public function children() { return $this->hasMany(Document::class, 'parent_id'); }
    public function parent() { return $this->belongsTo(Document::class, 'parent_id'); }

    public function getFileSizeHumanAttribute(): string
    {
        if ($this->type === 'folder') return '--';
        $bytes = $this->file_size ?? 0;
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
        return round($bytes / 1048576, 1) . ' MB';
    }
}

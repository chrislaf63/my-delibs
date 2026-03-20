<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $casts = [
        'indexed_at' => 'datetime',
    ];

    protected $fillable = [
        'council_id',
        'uploaded_by',
        'parent_document_id',
        'type',
        'title',
        'file_path',
        'original_filename',
        'mime_type',
        'file_size',
        'content',
        'status',
        'indexed_at',
    ];

    public function council()
    {
        return $this->belongsTo(Council::class);
    }

    public function uploader()
    {
        return $this->belongsTo(\App\Models\User::class, 'uploaded_by');
    }

    public function parent()
    {
        return $this->belongsTo(Document::class, 'parent_document_id');
    }

    public function annexes()
    {
        return $this->hasMany(Document::class, 'parent_document_id');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'indexed');
    }
}

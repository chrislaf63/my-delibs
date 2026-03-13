<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Council extends Model
{
    protected $fillable = ['council_date', 'reference'];
    protected $casts = [
        'council_date' => 'date',
    ];

    public function documents() {
        return $this->hasMany(Document::class);
    }

    protected static function booted(): void
    {
        static::deleting(function (Council $council) {
            $council->documents->each(fn ($doc) => Storage::delete($doc->file_path));
        });
    }
}

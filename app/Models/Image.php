<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Image extends Model
{
    use HasFactory;
    protected $fillable = [
        'path',
        'product_id',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    protected $appends = ['url']; // Add 'url' to always include it in JSON

    public function getUrlAttribute()
    {
        return url("storage/{$this->path}");
    }

}

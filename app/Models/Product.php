<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;
    protected $fillable=[
        'en_name',
        'ar_name',
        'en_description',
        'ar_description',
        'price',
        'quantity',
        'store_id'
    ];
    public function store():BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
    public function orders():BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_products')
                    ->withPivot('price', 'quantity')
                    ->withTimestamps();
    }
    public function images():HasMany
    {
        return $this->hasMany(Product::class);
    }
    public function carts():BelongsToMany
    {
        return $this->belongsToMany(Cart::class, 'cart_products')
                    ->withPivot('price', 'quantity')
                    ->withTimestamps();
    }
    public function favoties():BelongsToMany
    {
        return $this->belongsToMany(Favorite::class, 'favorite_products')
                    ->withTimestamps();
    }


}

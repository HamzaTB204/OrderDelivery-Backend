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
        'store_id',
        'orders_count'
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
        return $this->hasMany(Image::class);
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
    public function Quantity($quantity)
    {
        if ($this->quantity >= $quantity) {
            $this->quantity -= $quantity;
            $this->save();
            return true;
        }
        return false;
    }
    public function updateQuantity($quantity,$new_quantity)
    {
        $this->quantity+=$quantity;
        if ($this->quantity- $new_quantity >= 0) {
            $this->quantity -= $new_quantity;
            $this->save();
            return true;
        }
        return false;
    }

    public function scopeSearch($query, $search)
    {
        $search = "%$search%";
        return $query->where('en_name', 'like', $search)
                     ->orWhere('ar_name', 'like', $search)
                     ->orWhere('en_description', 'like', $search)
                     ->orWhere('ar_description', 'like', $search);
    }

}

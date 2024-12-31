<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Order extends Model
{
    use HasFactory;
    protected $fillable=['user_id','status'];

    public static array $status = ['pending', 'delivering', 'delivered','canceled'];

    public function user() {
        return $this->belongsTo(User::class);
    }
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'order_products')
                    ->withPivot('quantity', 'price')
                    ->withTimestamps();
    }
    public function updateStatus($newStatus)
    {
        if (in_array($newStatus, self::$status)) {
            $this->status = $newStatus;
            $this->save(); 
            return true; 
        }
        return false; 
    }
}

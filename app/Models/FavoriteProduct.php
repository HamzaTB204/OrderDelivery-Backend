<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class FavoriteProduct extends Pivot
{
    use HasFactory;
    protected $table = 'favorite_products';
    protected $fillable=['favorite_id','product_id',];

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model {

    use HasFactory;

    protected $fillable = ['name','logo'];

    public function products() {
        return $this->hasMany( Product::class );
    }
    public function scopeSearch($query, $search)
    {
        $search = "%$search%";
        return $query->where('name', 'like', $search);
    }

}

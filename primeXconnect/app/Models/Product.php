<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'code'
    ];

    public function stocks()
    {
        return $this->hasMany(Stock::class, 'product_id', 'id');
    }

    public function numberOfStocksOnHand()
    {
        return $this->stocks()->sum('on_hand');
    }

    public function numberOfStocksTaken()
    {
        return $this->stocks()->sum('taken');
    }
}

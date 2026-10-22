<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PriceHistory extends Model
{
    protected $fillable = ['price_item_id', 'price', 'recorded_at'];

    protected $casts = [
        'price'       => 'float',
        'recorded_at' => 'datetime',
    ];

    public $timestamps = false;

    public function item()
    {
        return $this->belongsTo(PriceItem::class, 'price_item_id');
    }
}

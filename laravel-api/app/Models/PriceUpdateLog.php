<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PriceUpdateLog extends Model
{
    protected $fillable = [
        'price_item_id', 'updated_by', 'old_price', 'new_price',
        'change', 'change_percent', 'source',
    ];

    protected $casts = [
        'old_price'       => 'float',
        'new_price'       => 'float',
        'change'          => 'float',
        'change_percent'  => 'float',
    ];

    public function item()
    {
        return $this->belongsTo(PriceItem::class, 'price_item_id');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}

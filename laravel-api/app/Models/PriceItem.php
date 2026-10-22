<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class PriceItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'price_category_id',
        'name',
        'name_urdu',
        'unit',
        'unit_urdu',
        'price',
        'previous_price',
        'price_change',
        'change_percent',
        'image_url',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'price'          => 'float',
        'previous_price' => 'float',
        'price_change'   => 'float',
        'change_percent' => 'float',
        'is_active'      => 'boolean',
        'deleted_at'     => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(PriceCategory::class, 'price_category_id');
    }

    public function history()
    {
        return $this->hasMany(PriceHistory::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class PriceCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'name_urdu', 'slug', 'icon', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'deleted_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(PriceItem::class);
    }
}

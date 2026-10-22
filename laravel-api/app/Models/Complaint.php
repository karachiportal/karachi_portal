<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Complaint extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'complaint_number',
        'full_name',
        'cnic',
        'mobile',
        'item_name',
        'shop_name',
        'latitude',
        'longitude',
        'location_address',
        'details',
        'status',
        'admin_remarks',
    ];

    protected $casts = [
        'latitude'   => 'float',
        'longitude'  => 'float',
        'deleted_at' => 'datetime',
    ];

    // ── Relationships ──
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pictures()
    {
        return $this->hasMany(ComplaintPicture::class);
    }

    public function statusLogs()
    {
        return $this->hasMany(ComplaintStatusLog::class, 'complaint_id')->orderBy('created_at');
    }

    // ── Scopes ──
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }
}

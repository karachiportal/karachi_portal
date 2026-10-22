<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComplaintStatusLog extends Model
{
    protected $fillable = [
        'complaint_id',
        'old_status',
        'new_status',
        'changed_by',
        'remarks',
        'created_at',
    ];

    public $timestamps = false;

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function complaint()
    {
        return $this->belongsTo(Complaint::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}

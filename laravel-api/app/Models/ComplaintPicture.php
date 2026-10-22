<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComplaintPicture extends Model
{
    protected $fillable = ['complaint_id', 'path', 'url'];

    public function complaint()
    {
        return $this->belongsTo(Complaint::class);
    }
}

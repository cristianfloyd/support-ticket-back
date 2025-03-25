<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Attachment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ticket_id',
        'filename',
        'path',
        'mime_type',
        'size'
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }
}
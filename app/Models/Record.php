<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Record extends Model
{
    protected $fillable = ['client_id', 'filename', 'duration'];
    //
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}

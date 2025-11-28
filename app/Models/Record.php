<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Record extends Model
{
    protected $fillable = ['participant_id', 'canary_assessment_id', 'duration'];
    //
    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }
}

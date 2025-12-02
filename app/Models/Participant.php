<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Participant extends Authenticatable
{
    use HasUuids;
    use HasApiTokens;
    protected $fillable = ['cc','name','canary_subject_id'];
    // No buscamos password
    public function getAuthPassword() {
        return null; 
    }
    public function records()
    {
        return $this->hasMany(Record::class);
    }
}
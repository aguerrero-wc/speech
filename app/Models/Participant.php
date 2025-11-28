<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Participant extends Authenticatable
{
    use HasUuids;
    protected $fillable = ['cc','canary_subject_id'];
    // Esto es importante para que Laravel sepa que no buscamos password
    public function getAuthPassword() {
        return null; 
    }
    public function records()
    {
        return $this->hasMany(Record::class);
    }
}
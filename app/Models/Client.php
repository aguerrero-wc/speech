<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Client extends Authenticatable
{
    protected $fillable = ['cedula', 'name'];
    // Esto es importante para que Laravel sepa que no buscamos password
    public function getAuthPassword() {
        return null; 
    }
    public function records()
    {
        return $this->hasMany(Record::class);
    }
}
<?php

namespace App\Models;

use App\Permission;

use Illuminate\Database\Eloquent\Model;

class Permission_father extends Model
{
    protected $table = 'permissions_father';

    public function permissions()
    {
        return $this->hasMany('App\Permission');
    }
}
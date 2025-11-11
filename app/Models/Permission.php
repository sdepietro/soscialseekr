<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $table = 'permissions';

    public function permissionFather()
    {
        return $this->hasOne('App\Permission_father','id','permission_father_id');
    }

}
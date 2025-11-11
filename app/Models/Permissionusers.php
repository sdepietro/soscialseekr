<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permissionusers extends Model
{
    protected $table = 'permissions_users';

    public function permission()
    {
        return $this->belongsTo('App\Permissions')->select('*');
    }

    public function user()
    {
        return $this->belongsTo('App\User')->select('*');
    }
}
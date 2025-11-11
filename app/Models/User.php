<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    public function travels()
    {
        return $this->hasMany(\App\Models\Travel::class);
    }

    /**
     * Relación con empresa
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Relación con onboarding
     */
    public function onboarding()
    {
        return $this->hasOne(UserOnboarding::class);
    }

    /**
     * Relación con búsquedas
     */
    public function searches()
    {
        return $this->hasMany(Search::class);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'name',
        'lastname',
        'role',
        'email',
        'password',
        'position',
        'is_company_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


    /**
     * Metopdo para determinar si un usuario tiene permisos para acceder a alguna seccion en particular.
     * Se usa en el @can y en el middleware del routing
     * @param $permission
     * @return bool
     */
    public function checkAccess($permission)
    {
//        return Cache::remember("user_{$this->id}_permission_{$permission}", 3600, function () use ($permission) {
//            $oPermission = Permission::where('tag', $permission)->first();
//
//            if (empty($oPermission)) {
//                return false;
//            }
//
//            return Permissionusers::where('permission_id', $oPermission->id)
//                ->where('user_id', $this->id)
//                ->exists();
//        });

        $role = $this->role ?? null;
        if (!$role) {
            return false;
        }

        $rolePermissions = (array) config('constants.permissions_by_role', []);

        // Wildcard
        if (in_array('*', $rolePermissions[$role] ?? [], true)) {
            return true;
        }

        return in_array($permission, $rolePermissions[$role] ?? [], true);


    }


    /*
     Añadiremos estos dos métodos para JWT
    */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }


}

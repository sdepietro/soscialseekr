<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Search extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'searchs'; // nombre no convencional
    protected $guarded = [];

    protected $casts = [
        'active'            => 'boolean',
        'only_from_accounts'=> 'array',
        'last_run_at'       => 'datetime',
    ];

    // Scopes útiles
    public function scopeActive($q)
    {
        return $q->where('active', true);
    }

    public function scopeCountry($q, string $country = 'AR')
    {
        return $q->where('country', $country);
    }

    /**
     * Relación con usuario
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con tweets encontrados por esta búsqueda
     */
    public function tweets()
    {
        return $this->hasMany(Tweet::class, 'search_id');
    }
}

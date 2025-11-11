<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use SoftDeletes;

    protected $table = 'accounts';

    protected $fillable = [
        'twitter_id','username','name','url',
        'is_blue_verified','verified_type',
        'profile_picture','cover_picture',
        'description','location','followers','following','can_dm',
        'created_at_twitter','favourites_count','has_custom_timelines',
        'is_translator','media_count','statuses_count',
        'withheld_in_countries','affiliates_highlighted_label','possibly_sensitive',
        'pinned_tweet_ids','is_automated','automated_by',
        'unavailable','message','unavailable_reason',
        'profile_bio_description','profile_bio_entities','raw_payload'
    ];

    protected $casts = [
        'is_blue_verified' => 'bool',
        'can_dm' => 'bool',
        'has_custom_timelines' => 'bool',
        'is_translator' => 'bool',
        'possibly_sensitive' => 'bool',
        'is_automated' => 'bool',
        'unavailable' => 'bool',
        'created_at_twitter' => 'datetime',
        'withheld_in_countries' => 'array',
        'affiliates_highlighted_label' => 'array',
        'pinned_tweet_ids' => 'array',
        'profile_bio_entities' => 'array',
        'raw_payload' => 'array',
    ];

    public function tweets()
    {
        return $this->hasMany(Tweet::class, 'account_id');
    }
}

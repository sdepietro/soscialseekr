<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tweet extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tweets';
    protected $guarded = [];

    protected $casts = [
        'retweet_count'       => 'integer',
        'reply_count'         => 'integer',
        'like_count'          => 'integer',
        'quote_count'         => 'integer',
        'view_count'          => 'integer',
        'bookmark_count'      => 'integer',
        'is_reply'            => 'boolean',
        'is_limited_reply'    => 'boolean',
        'display_text_range'  => 'array',
        'entities'            => 'array',
        'quoted_tweet'        => 'array',
        'retweeted_tweet'     => 'array',
        'matched_search_ids'  => 'array',
        'raw_payload'         => 'array',
        'created_at_twitter'  => 'datetime',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function search()
    {
        return $this->belongsTo(Search::class, 'search_id');
    }

    public function histories()
    {
        return $this->hasMany(TweetHistory::class, 'tweet_id');
    }

    /**
     * Accessor para obtener la URL del tweet en X/Twitter
     */
    public function getUrlAttribute(): string
    {
        if (!$this->account || !$this->twitter_id) {
            return '';
        }

        return "https://x.com/{$this->account->username}/status/{$this->twitter_id}";
    }

    /**
     * Accessor para obtener created_at_twitter en timezone de Buenos Aires
     * La fecha se guarda en UTC en la BD, pero se muestra en GMT-3
     */
    public function getCreatedAtLocalAttribute()
    {
        return $this->created_at_twitter?->timezone('America/Argentina/Buenos_Aires');
    }
}

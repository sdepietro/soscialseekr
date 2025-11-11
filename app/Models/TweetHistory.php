<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TweetHistory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tweets_history';
    protected $guarded = [];

    protected $casts = [
        'retweet_count'     => 'integer',
        'reply_count'       => 'integer',
        'like_count'        => 'integer',
        'quote_count'       => 'integer',
        'view_count'        => 'integer',
        'bookmark_count'    => 'integer',
        'previous_snapshot' => 'array',
        'new_snapshot'      => 'array',
        'diff'              => 'array',
        'changed_at'        => 'datetime',
    ];

    public function tweet()
    {
        return $this->belongsTo(Tweet::class, 'tweet_id');
    }
}

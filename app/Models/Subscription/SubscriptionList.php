<?php

namespace App\Models\Subscription;

use Illuminate\Database\Eloquent\Model;

class SubscriptionList extends Model
{
    /** @var string */
    protected $table = 'subscriptions_list';

    protected $fillable = [
        'price',
        'duration',
        'one_time',
        'title',
        'old_price',
        'count_letters',
        'count_watch_or_send_photos',
        'count_watch_or_send_video'
    ];
}

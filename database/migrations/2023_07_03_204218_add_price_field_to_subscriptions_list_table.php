<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Subscription\SubscriptionList;

return new class extends Migration
{
    private $data = [
        [
            'price' => 5,
            'duration' => 10,
            'one_time' => 1,
            'title' => 'Единовременная подписка на 10 минут',
            'count_letters' => 20,
            'count_watch_or_send_photos' => 1,
            'count_watch_or_send_video' => 0
        ],
        [
            'price' => 30,
            'duration' => 60,
            'one_time' => 0,
            'title' => 'Подписка на час',
            'count_letters' => 120,
            'count_watch_or_send_photos' => 5,
            'count_watch_or_send_video' => 3
        ],
        [
            'price' => 180,
            'duration' => 360,
            'one_time' => 0,
            'title' => 'Подписка на 6 часов',
            'count_letters' => 720,
            'count_watch_or_send_photos' => 15,
            'count_watch_or_send_video' => 5
        ],
        [
            'price' => 360,
            'duration' => 720,
            'one_time' => 0,
            'title' => 'Подписка на 12 часов',
            'count_letters' => 1440,
            'count_watch_or_send_photos' => 25,
            'count_watch_or_send_video' => 5
        ]
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('subscriptions_list', function (Blueprint $table) {
            $table->double('price')->default(0);
            $table->dropColumn('hours');
            $table->dropColumn('services_list');
            $table->dropColumn('days');
            $table->dropColumn('months');
            $table->unsignedBigInteger('duration')->comment('Minutes');
            $table->boolean('one_time')->default(false);
            $table->unsignedBigInteger('count_letters')->default(0);
            $table->unsignedBigInteger('count_watch_or_send_photos')->default(0);
            $table->unsignedBigInteger('count_watch_or_send_video')->default(0);
        });

        SubscriptionList::query()->delete();

        foreach ($this->data as $item) {
            SubscriptionList::create($item);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions_list', function (Blueprint $table) {
            $table->dropColumn('price');
            $table->unsignedBigInteger('hours')->nullable();
            $table->unsignedBigInteger('days')->nullable();
            $table->unsignedBigInteger('months')->nullable();
            $table->dropColumn('duration');
            $table->dropColumn('one_time');
            $table->dropColumn('count_letters');
            $table->dropColumn('count_watch_or_send_photos');
            $table->dropColumn('count_watch_or_send_video');
        });
    }
};

<?php

namespace App\Http\Controllers\API\V1;

use App\Enum\Subscription\SubscriptionListEnum;
use App\Enum\Subscription\SubscriptionServiceEnum;
use App\Http\Controllers\Controller;
use App\Models\Subscriptions;
use App\Repositories\Subscription\SubscriptionRepository;
use App\Services\Subscription\SubscriptionService;
use App\Traits\UserSubscriptionTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SubscriptionController extends Controller
{
    /** @var UserSubscriptionTrait */
    private UserSubscriptionTrait $userSubscriptionTrait;

    /**
     * @var SubscriptionService
     */
    private SubscriptionService $subscriptionService;

    public function __construct(
        UserSubscriptionTrait $userSubscriptionTrait,
        SubscriptionService $subscriptionService
    )
    {
        $this->userSubscriptionTrait = $userSubscriptionTrait;
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * @return JsonResponse
     */
    public function index()
    {
        $subscription = $this->subscriptionService->getUserCurrentSubscription(Auth::user());

        return response()->json($subscription);
    }
}

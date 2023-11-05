<?php

namespace App\Http\Middleware;

use App\Models\StatisticSite\UserInputs;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;


class UpdateLastActivityMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle($request, Closure $next)
    {
        $lsitForOnline = [
            'App\Http\Controllers\API\V1\ChatController',
            'App\Http\Controllers\API\V1\LetterController',
            'App\Http\Controllers\API\V1\ImageController',
            'App\Http\Controllers\API\V1\CreditsController',
            'App\Http\Controllers\API\V1\ProfileController',
            'App\Http\Controllers\API\V1\AuthController',
            '\App\Http\Controllers\API\V1\Activities\AnketWatchController',
            '\App\Http\Controllers\API\V1\Activities\AnketFavoriteController',
            '\App\Http\Controllers\API\V1\Activities\AnketLikeController',
            '\App\Http\Controllers\API\V1\CreditsController',
            '\App\Http\Controllers\PaymentController'
        ];

        $user = Auth::user();
        $controller = $request->route()->getAction()['controller'];
        if(isset($user) &&  !$user->online  && Str::startsWith($controller,$lsitForOnline)){
            $in = new UserInputs();
            $in->user_id = $user->id;
            $in->save();
        }
        if (isset($user) &&  !$user->online  && Str::startsWith($controller,$lsitForOnline)) {
            $user->updated_at = now();
            $user->online = true;
            $user->save();
        }
        return $next($request);
    }
}

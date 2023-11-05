<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {

        if(trim(request()->route()->getPrefix(),'/') == 'acquiring'){
            return route('acquiring.login');
        }

        if(trim(request()->route()->getPrefix(),'/') == 'management'){
            return route('management.login');
        }

        if(trim(request()->route()->getPrefix(),'/') == 'bank-admin'){
            return route('bank-admin.login');
        }

        return $request->expectsJson() ? null : route('login');
    }

}

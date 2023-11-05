<?php


namespace App\Http\Controllers\API\V1\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;

class SystemRouteController extends Controller{

    public function getAllRoute(){
        return response()->json(Route::getRoutes()->getRoutes());
    }
}

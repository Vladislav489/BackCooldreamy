<?php

namespace App\Http\Controllers;

use App\Enum\Image\ImageStatusEnum;
use App\Models\Image;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AcquiringController extends Controller
{
    public function loginPage()
    {
        return view('acquiring.login');
    }

    public function login(Request $request)
    {
        if (!Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            return back()->withErrors(['error' => 'Invalid credentials']);
        }

        if (!Auth::user()->hasRole('acquiring')) {
            Auth::logout();
            return back()->withErrors(['error' => 'Action forbidden']);
        }

        return redirect()->route('acquiring.index');
    }

    public function index()
    {
        $type = 'Last';
        $images = Image::query()->with('user', 'category')->where('status', ImageStatusEnum::NEW)->orderByDesc('updated_at')->paginate(40);

        return view('acquiring.index', compact('images', 'type'));
    }

    public function blocked()
    {
        $type = 'Blocked';
        $images = Image::query()->with('user', 'category')->where('status', ImageStatusEnum::BLOCKED)->orderByDesc('updated_at')->paginate(40);

        return view('acquiring.index', compact('images', 'type'));
    }

    public function accepted()
    {
        $type = 'Accepted';
        $images = Image::query()->with('user', 'category')->where('status', ImageStatusEnum::ACCEPTED)->orderByDesc('updated_at')->paginate(40);

        return view('acquiring.index', compact('images', 'type'));
    }

    public function accept(Image $image)
    {
        $image->status = ImageStatusEnum::ACCEPTED;
        $image->save();
        $user = $image->user;
        $user->is_blocked = false;
        $user->save();

        return redirect()->route('acquiring.accepted');
    }

    public function block(Image $image)
    {
        $image->status = ImageStatusEnum::BLOCKED;
        $image->save();
        $user = $image->user;
        $user->is_blocked = true;
        $user->save();

        return redirect()->route('acquiring.blocked');
    }

    public function logout()
    {
        Auth::logout();

        return redirect()->route('acquiring.login');
    }
}

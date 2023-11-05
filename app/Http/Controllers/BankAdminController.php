<?php

namespace App\Http\Controllers;

use App\Enum\Anket\AnketStatusEnum;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BankAdminController extends Controller
{
    public function loginPage()
    {
        return view('bank-admin.login');
    }

    public function login(Request $request)
    {
        if (!Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            return back()->withErrors(['error' => 'Invalid credentials']);
        }

        if (!Auth::user()->hasRole('bank-admin')) {
            Auth::logout();
            return back()->withErrors(['error' => 'Action forbidden']);
        }

        return redirect()->route('bank-admin.index');
    }

    public function index(Request $request)
    {
        $users = User::query()->where('is_real', false)->where('gender', 'female')->where('anket_status', AnketStatusEnum::NEW);

        if ($request->has('search') && $request->get('search')) {
            $users->where('name', 'like', "%" . $request->get('search'). "%");
        }

        $users = $users->orderByDesc('updated_at')->paginate(30);

        return view('bank-admin.index', compact('users'));
    }

    public function show($id)
    {
        $user = User::query()->where('is_real', false)->where('gender', 'female')->findOrFail($id);

        return view('bank-admin.show', compact('user'));
    }

    public function logout()
    {
        Auth::logout();

        return redirect()->route('bank-admin.login');
    }
}

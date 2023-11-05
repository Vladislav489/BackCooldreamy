<?php

namespace App\Http\Controllers;

use App\Enum\Anket\AnketStatusEnum;
use App\Enum\Image\AnketOperatorFileType;
use App\Models\Anket\AnketFile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class ManagementController extends Controller
{
    public function loginPage()
    {
        return view('management.login');
    }

    public function login(Request $request)
    {
        if (!Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            return back()->withErrors(['error' => 'Invalid credentials']);
        }

        if (!Auth::user()->hasRole('management')) {
            Auth::logout();
            return back()->withErrors(['error' => 'Action forbidden']);
        }

        return redirect()->route('management.index');
    }

    public function index(Request $request)
    {
        $users = User::query()->where('is_real', false)->where('gender', 'female')->where('anket_status', AnketStatusEnum::NEW);

        if ($request->has('search') && $request->get('search')) {
            $users->where('name', 'like', "%" . $request->get('search'). "%");
        }

        $users = $users->orderByDesc('updated_at')->paginate(30);

        return view('management.index', compact('users'));
    }

    public function show($id)
    {
        $user = User::query()->where('is_real', false)->where('gender', 'female')->findOrFail($id);

        return view('management.show', compact('user'));
    }


    public function logout()
    {
        Auth::logout();

        return redirect()->route('acquiring.login');
    }

    public function uploadPhoto(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|image',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator->errors());
        }

        $file = $request->file('file');
        if (!$file) {
            return back()->withErrors(['error' => 'Choose File']);
        }

        if ($user->anketPhotos()->count() >= 3) {
            return back()->withErrors(['error' => 'Error, Maximum files: 3']);
        }

        $str = Str::uuid();

        $file->storeAs("public/images/management/{$user->id}", "$str.{$file->extension()}");

        AnketFile::create([
            'anket_id' => $user->id,
            'type' => AnketOperatorFileType::PHOTO,
            'path' => "public/images/management/{$user->id}/$str.{$file->extension()}",
            'url' => "https://media.cooldreamy.com/management/{$user->id}/$str.{$file->extension()}"
        ]);

        return back()->with(['message' => 'Successfully added photo']);
    }

    public function deletePhoto($userId, $id)
    {
        $user = User::query()->where('is_real', false)->where('gender', 'female')->findOrFail($userId);

        $anketFile = AnketFile::query()->where('anket_id', $user->id)->where('type', AnketOperatorFileType::PHOTO)->findOrFail($id);
        Storage::delete($anketFile->path);
        $anketFile->delete();

        return back()->with(['message' => 'Successfully delete photo']);
    }


    public function uploadVideo(Request $request, User $user)
    {

        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:mp4,mov,avi',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator->errors());
        }

        if ($user->anketVideos()->count() >= 3) {
            return back()->withErrors(['error' => 'Maximum files: 3']);
        }

        $file = $request->file('file');
        if (!$file) {
            return back()->withErrors(['error' => 'Choose File']);
        }

        $str = Str::uuid();

        $file->storeAs("public/images/management/{$user->id}", "$str.{$file->extension()}");

        AnketFile::create([
            'anket_id' => $user->id,
            'type' => AnketOperatorFileType::VIDEO,
            'path' => "public/images/management/{$user->id}/$str.{$file->extension()}",
            'url' => "https://media.cooldreamy.com/management/{$user->id}/$str.{$file->extension()}"
        ]);

        return back();
    }

    public function deleteVideo($userId, $id)
    {
        $user = User::query()->where('is_real', false)->where('gender', 'female')->findOrFail($userId);

        $anketFile = AnketFile::query()->where('anket_id', $user->id)->where('type', AnketOperatorFileType::VIDEO)->findOrFail($id);
        Storage::delete($anketFile->path);
        $anketFile->delete();

        return back()->with(['message' => 'Successfully delete video']);
    }
}

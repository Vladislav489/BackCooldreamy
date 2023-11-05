<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Setting;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class PageController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $page = Page::query()
            ->where('url', $request->get('url'))
            ->first();

        return response()->json($page);
    }

    public function adminData()
    {
        $users = Page::select('*')->orderBy('created_at', 'desc');

        return DataTables::of($users)->make();
    }

    /**
     * @param Request $request
     * @param $page
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $page)
    {
        if (!$request->text_ru || !$request->text_en) {
            return back();
        }

        $page = Page::findOrFail($page);
        $page->text_ru = $request->text_ru;
        $page->text_en = $request->text_en;
        $page->save();

        return back();
    }

    public function adminShow($id)
    {
        $page = Page::findOrFail($id);

        return view('admin.pages.show', compact('page'));
    }

    public function adminIndex()
    {
        return view('admin.pages.index');
    }

    public function settings()
    {
        $settings = Setting::query()->where('name', 'like', '%checker%')->limit(6)->get();

        $settingGeos = Setting::query()->where('name', 'like','%geo%')->limit(6)->get();

        foreach ([1,2,3,4,5,6,7,8,9,10] as $i) {
            if (!$settings->where('name', 'checker' . $i)->first()) {
                $setting = Setting::create([
                    'name' => 'checker'.$i,
                    'value' => 0
                ]);
            }

            if (!$settingGeos->where('name', 'geo'.$i)->first()) {
                $settingGeo = Setting::create([
                    'name' => 'geo'.$i,
                    'value' => 0
                ]);
            }
        }

        return view('admin.settings', compact('settings', 'settingGeos'));
    }

    public function changeSetting(Request $request)
    {
        $value = $request->value == 'on' ? 1 : 0;
        Setting::query()->where('name', $request->name)->update(['value' => $value]);

        return back();
    }

    public function getSetting($id)
    {
        $value = Setting::query()->where('name', 'checker'.$id)->first()->value ?? 0;

        return response()->json(['value' => $value]);
    }

    public function getSettinggeo($id)
    {
        $value = Setting::query()->where('name', 'geo'.$id)->first()->value ?? 0;

        return response()->json(['value' => $value]);
    }
}

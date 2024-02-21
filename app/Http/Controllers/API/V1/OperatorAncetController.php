<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Image;
use App\Models\OperatorChatLimit;
use App\Models\User;
use App\Models\Video;
use App\Repositories\Operator\AnketRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class OperatorAncetController extends Controller
{
    private AnketRepository $anketRepository;

    public function __construct(AnketRepository $anketRepository)
    {
        $this->anketRepository = $anketRepository;
    }

    /**
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $operator = Auth::user();
        $users = $this->anketRepository->index($operator);

        return response()->json($users);
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function media(Request $request, $id){
        $user = User::find($id);
        $result = Image::where('user_id', $user->id);
        if ($request->get('category_id')) {
            $result->where('category_id', $request->get('category_id'));
        }
        if ($request->get('category_id') == 'video') {
            $result = Video::where('user_id', $user->id);
        }
        $result = $result->paginate(5);
        return response()->json($result);
    }

    public function manInfo(Request $request, $id)
    {
        $user = User::query()->where('gender', 'male')->where('is_real', true)->findOrFail($id);

        $user->load(['prompt_targets', 'prompt_interests', 'prompt_finance_states', 'prompt_sources', 'prompt_want_kids', 'prompt_relationships', 'prompt_careers']);

        $user->profile_photo = Image::where('user_id', $user->id)
            ->where('category_id', 2)
            ->get();

        return response($user);
    }

    public function info(Request $request, $id)
    {
        $user = User::query()
            ->selectRaw("(SELECT COUNT(*) FROM images WHERE category_id = 2  AND  user_id = users.id ) as 'profile'  ,
            (SELECT COUNT(*) FROM images WHERE category_id = 3  AND  user_id = users.id)  as 'content',
            (SELECT COUNT(*) FROM images WHERE category_id = 4  AND  user_id = users.id) as '18+',
            (SELECT COUNT(*) FROM images WHERE category_id = 5  AND  user_id = users.id) as 'public'
            ")
            ->where('gender', '=','female')
            ->where('is_real', '=','0')->findOrFail($id);

        $user->load(['prompt_targets', 'prompt_interests', 'prompt_finance_states', 'prompt_sources', 'prompt_want_kids', 'prompt_relationships', 'prompt_careers']);

        $user->profile_photo = Image::where('user_id', $user->id)
            ->where('category_id', 2)
            ->get();

        return response($user);
    }

    public function getMan($id, $manId)
    {
        $user = User::query()->where('gender', 'female')->where('is_real', false)->findOrFail($id);
        $man = User::query()->findOrFail($manId);

        return [
            'anket' => $user,
            'man' => $man
        ];
    }

}

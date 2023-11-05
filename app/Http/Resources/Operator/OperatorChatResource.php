<?php

namespace App\Http\Resources\Operator;

use App\Models\FavoriteProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use function Symfony\Component\Translation\t;

class OperatorChatResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'self' => !$this->firstUser->is_real ? $this->secondUser : $this->firstUser,
            'another_user' => $this->firstUser->is_real ? $this->secondUser : $this->firstUser,
        ]);

//        // todo оптимизация легкая
//        $favoriteUsers = FavoriteProfile::where('user_id', Auth::id())
//            ->where('disabled', false)
//            ->pluck('favorite_user_id');
//
//        return array_merge(Arr::except(parent::toArray($request), ['first_user', 'second_user']), [

//            'last_message' => $this->lastMessage,
//            'is_new' => true,
//            'favorite' => ($favoriteUsers->contains($this->first_user_id) || $favoriteUsers->contains($this->second_user_id)) ? 1 : 0
//        ]);
    }
}

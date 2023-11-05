<?php

namespace App\Repositories\Rating;

use App\Models\Rating;
use App\Repositories\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;

/**
  * Ryzhakov Alexey 2023-06-20
  * Репозиторий Рейтинга пользователя
 */
class RatingRepository implements RepositoryInterface
{
    /**
     * @param array $data
     * @return Rating
     */
    public function store(array $data = []): Rating
    {
       return Rating::create(
           $data
       );
    }

    /**
     * @param int $id
     * @return Rating
     */
    public function find(int $id): Rating
    {
       return Rating::findOrFail($id);
    }

    /**
     * @param Model $entity
     * @param array $data
     * @return Rating
     */
    public function update(Model $entity, array $data = []): Rating
    {
        $entity->fill($data);

        $entity->save();
        return $entity;
    }

    /**
     * @param int $id
     * @return bool|null
     */
    public function delete(int $id): bool|null
    {
        return Rating::where('id', $id)->delete();
    }
}

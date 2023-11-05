<?php

namespace App\Repositories\Rating;

use App\Models\RatingHistory;
use App\Repositories\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;

/**
 *
 */
class RatingHistoryRepository implements RepositoryInterface
{
    /**
     * @param array $data
     * @return RatingHistory
     */
    public function store(array $data = []): RatingHistory
    {
        return RatingHistory::create(
            $data
        );
    }

    /**
     * @param int $id
     * @return RatingHistory
     */
    public function find(int $id): RatingHistory
    {
        return RatingHistory::findOrFail($id);
    }

    /**
     * @param Model $entity
     * @param array $data
     * @return RatingHistory
     */
    public function update(Model $entity, array $data = []): RatingHistory
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
        return RatingHistory::where('id', $id)->delete();
    }
}

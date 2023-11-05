<?php

namespace App\Repositories\Rating;

use App\Models\RatingAssignment;
use App\Repositories\RepositoryInterface;
use Database\Seeders\RatingAssignmentTableSeeder;
use Illuminate\Database\Eloquent\Model;

/**
 * Ryzhakov Alexey 2023-06-20
 *
 * Репозиторий для настроек по рейтингам
 * Тут указываются за какие рейтинги какой баланс имеют
 * @see RatingAssignmentTableSeeder
 * @see RatingAssignment
 */
class RatingAssignmentRepository implements RepositoryInterface
{
    /**
     * @param array $data
     * @return RatingAssignment
     */
    public function store(array $data = []): RatingAssignment
    {
        return RatingAssignment::create(
            $data
        );
    }

    /**
     * @param int $id
     * @return RatingAssignment
     */
    public function find(int $id): RatingAssignment
    {
        return RatingAssignment::findOrFail($id);
    }

    /**
     * @param Model $entity
     * @param array $data
     * @return RatingAssignment
     */
    public function update(Model $entity, array $data = []): RatingAssignment
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
        return RatingAssignment::where('id', $id)->delete();
    }
}

<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;

interface RepositoryInterface
{
    /**
     * Сохранение сущности
     *
     * @param array $data
     * @return mixed
     */
    public function store(array $data = []): mixed;

    /**
     * Поиск записи по id
     *
     * @param int $id
     * @return mixed
     */
    public function find(int $id): mixed;

    /**
     * Обновление записи по id
     *
     * @param Model $entity
     * @param array $data
     * @return mixed
     */
    public function update(Model $entity, array $data = []): mixed;

    /**
     * Удаление записи по id
     *
     * @param int $id
     * @return bool|null
     */
    public function delete(int $id): bool|null;
}

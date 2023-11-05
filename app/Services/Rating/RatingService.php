<?php

namespace App\Services\Rating;

use App\Enum\Rating\RatingAssignmentEnum;
use App\Models\User;
use App\Repositories\Rating\RatingAssignmentRepository;
use App\Repositories\Rating\RatingHistoryRepository;
use App\Repositories\Rating\RatingRepository;

/**
 * Ryzhakov Alexey 2023-06-20
 * Работа с рейтингом пользователя
 * Его сохранение и изменение выполняются вызовом метода saveUserRating, куда передаем пользователя и id присвоения
 * @see RatingAssignmentEnum
 */
class RatingService
{
    /** @var RatingAssignmentRepository */
    protected RatingAssignmentRepository $ratingAssignmentRepository;

    /** @var RatingHistoryRepository */
    protected RatingHistoryRepository $ratingHistoryRepository;

    /** @var RatingRepository */
    protected RatingRepository $ratingRepository;

    public function __construct(
        RatingRepository $ratingRepository,
        RatingHistoryRepository $ratingHistoryRepository,
        RatingAssignmentRepository $ratingAssignmentRepository,
    ) {
        $this->ratingRepository = $ratingRepository;
        $this->ratingAssignmentRepository = $ratingAssignmentRepository;
        $this->ratingHistoryRepository = $ratingHistoryRepository;
    }

    /**
     * @param User $user
     * @param $ratingAssignmentId
     * @return \App\Models\Rating|mixed
     */
    public function saveUserRating(User $user, $ratingAssignmentId)
    {
        $assignment = $this->ratingAssignmentRepository->find(
            $ratingAssignmentId
        );

        if (!$rating = $user->rating) {
            $rating = $this->ratingRepository->store([
                'user_id' => $user->id,
                'rating' => $assignment->limit
            ]);
        } else {
            $this->ratingRepository->update($rating, [
                'rating' => $rating->rating + $assignment->limit
            ]);
        }

        $this->ratingHistoryRepository->store([
            'assignment_id' => $assignment->id,
            'rating_id' => $rating->id
        ]);

        return $rating;
    }

    /**
     * @param User $user
     * @return array
     */
    public function getUserRating(User $user): array
    {
        $rating = $user->rating;

        if (!$rating) {
            $rating = $this->ratingRepository->store([
                'user_id' => $user->id,
                'rating' => 0
            ]);
        }

        return [
            'last_activity' => $this->getRatingHistoryLabel($rating->getLastActivityValue()),
            'activity' => [
                'value' => $rating->rating,
                'label' => $this->getRatingHistoryLabel($rating->value),
            ],
            'is_donate' => $user->is_donate,
        ];
    }

    /**
     * @param $value
     * @return string
     */
    public function getRatingHistoryLabel($value): string
    {
        if ($value > 0 && $value < 2) {
            return 'Nullable';
        }

        if ($value >= 2 && $value <= 50) {
            return 'Low';
        }

        if ($value >= 51 && $value <= 500) {
            return 'Normal';
        }

        if ($value >= 501 && $value < 1000) {
            return 'Middle';
        }

        if ($value >= 1001) {
            return 'High';
        }

        return 'Nullable';
    }
}

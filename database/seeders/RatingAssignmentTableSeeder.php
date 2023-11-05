<?php

namespace Database\Seeders;

use App\Enum\Rating\RatingAssignmentEnum;
use App\Models\RatingAssignment;
use App\Repositories\Rating\RatingAssignmentRepository;
use Illuminate\Database\Seeder;

class RatingAssignmentTableSeeder extends Seeder
{
    /** @var RatingAssignmentRepository */
    private RatingAssignmentRepository $ratingAssignmentRepository;

    public function __construct(RatingAssignmentRepository $ratingAssignmentRepository)
    {
        $this->ratingAssignmentRepository = $ratingAssignmentRepository;
    }

    /**
     * Запуск миграции
     */
    public function run()
    {
        $assignments = RatingAssignmentEnum::constants();

        foreach ($assignments as $assignment) {
            $this->ratingAssignmentRepository->store([
                'slug' => RatingAssignmentEnum::$ratingTexts[$assignment],
                'limit' => RatingAssignmentEnum::$ratings[$assignment],
            ]);
        }
    }
}

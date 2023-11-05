<?php

namespace Database\Factories;

use App\Enum\Anket\AnketStatusEnum;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake('ru_RU')->name('female'),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
            'state'=>fake('ru_RU')->city(),
            'country'=>'Россия',
            'gender'=>'female',
            'prompt_target_id'=>rand(1,4),
            'prompt_finance_state_id'=>rand(1,3),
            'prompt_source_id'=>rand(1,6),
            'prompt_want_kids_id'=>rand(1,4),
            'prompt_relationship_id'=>rand(2,3),
            'is_email_verified' => false,
            'prompt_career_id'=>rand(1,2),
            'anket_status' => AnketStatusEnum::NEW,
            'birthday'=>fake()->dateTimeBetween('1998-01-01', '2005-01-01'),
            'avatar_url'=>'https://zamanilka.ru/wp-content/uploads/2019/04/devushki-na-avu3-1080x1920-576x1024.jpg',
            'avatar_url_thumbnail'=>'https://avatars.mds.yandex.net/i?id=d82c3c24000ac9901c9cbfbd607177ac-5231618-images-thumbs&n=13',
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}

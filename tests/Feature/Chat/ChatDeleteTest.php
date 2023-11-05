<?php

namespace Tests\Feature\Chat;

use App\Models\Chat;
use App\Models\User;
use Tests\Feature\AbstractTestCase;

class ChatDeleteTest extends AbstractTestCase
{
    /** Название роута */
    public function getRouteName(): string
    {
        return 'chat.delete';
    }

    /**
     * Проверка авторизации
     * @test
     */
    public function test_authorization()
    {
        $user = User::factory()->create();
        $secondUser = User::factory()->create();
        $chat = Chat::factory()->create([
            'first_user_id' => $user->id,
            'second_user_id' => $secondUser->id
        ]);

        $url = $this->getRouteUrlByName(['id' => $chat->id]);

        $response = $this->deleteJson($url);

        $response->assertUnauthorized();
    }

    /**
     * Проверка роли
     * @test
     */
    public function test_exists()
    {
        $user = User::factory()->create();
        $secondUser = User::factory()->create();
        $chat = Chat::factory()->create([
            'first_user_id' => $user->id,
            'second_user_id' => $secondUser->id
        ]);
        $chatUser = User::factory()->create();

        $url = $this->getRouteUrlByName(['id' => $chat->id]);

        $response = $this->actingAs($chatUser)->deleteJson($url);

        $response->assertNotFound();
    }


    /**
     * Проверка работы эндпоинта
     * @test
     */
    public function test_endpoint()
    {
        $user = User::factory()->create();
        $secondUser = User::factory()->create();
        $chat = Chat::factory()->create([
            'first_user_id' => $user->id,
            'second_user_id' => $secondUser->id
        ]);

        $url = $this->getRouteUrlByName(['id' => $chat->id]);

        $response = $this->actingAs($user)->deleteJson($url);

        $response->assertOk();
    }

    /**
     * Проверка данных
     * @test
     */
    public function test_content()
    {
        $user = User::factory()->create();
        $secondUser = User::factory()->create();
        $chat = Chat::factory()->create([
            'first_user_id' => $user->id,
            'second_user_id' => $secondUser->id
        ]);

        $url = $this->getRouteUrlByName(['id' => $chat->id]);

        $response = $this->actingAs($user)->deleteJson($url);

        $this->assertDatabaseMissing('chats', [
            'id' => $chat->id
        ]);

        $response->assertOk();
    }
}

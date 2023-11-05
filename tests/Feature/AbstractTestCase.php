<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

abstract class AbstractTestCase extends TestCase
{
    use DatabaseTransactions, WithFaker;

    /** Название роута */
    abstract public function getRouteName(): string;

    /**
     * Получение роута
     */
    private function getRouteByName(): Route
    {
        $routes = Route::getRoutes();

        /** @var Route $route */
        $route = $routes->getByName($this->getRouteName());

        if (!$route) {
            $this->fail("Route with name [{$this->getRouteName()}] not found!");
        }

        return $route;
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(
            ThrottleRequests::class
        );
    }

    /**
     * Получение url
     */
    public function getRouteUrlByName(array $parameters = []): string
    {
        return route($this->getRouteName(), $parameters);
    }
}

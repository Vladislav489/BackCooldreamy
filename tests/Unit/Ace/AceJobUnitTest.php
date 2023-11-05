<?php

namespace Tests\Unit\Ace;
use App\Jobs\NewAceJob;
use App\Models\User;
use PHPUnit\Framework\TestCase;

class AceJobUnitTest extends TestCase
{
    public function test_job()
    {
        NewAceJob::dispatch(User::find(301));
    }
}

<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a temporary user
        // As this is inherently a one-user project, this should suffice
        // as no user ID is ever recorded.
        $this->user = User::factory()->make();
    }
}

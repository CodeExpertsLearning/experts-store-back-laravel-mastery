<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function makeUserToken(string $tokenName = 'default', array $abilities = ['*'])
    {
        $token = User::factory()->create();
        return $token->createToken($tokenName, $abilities)->plainTextToken;
    }
}

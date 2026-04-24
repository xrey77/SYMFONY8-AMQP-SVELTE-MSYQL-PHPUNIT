<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;

class LoginTest extends ApiTestCase
{
    public function testLoginSuccess(): void
    {
        $response = static::createClient()->request('POST', '/api/login', [
            'json' => [
                'username' => 'Rey',
                'password' => 'rey',
            ],
        ]);

        // Matches the 200 status code in your controller
        $this->assertResponseIsSuccessful();
        
        // Matches the 'message' and structure returned in your JsonResponse
        $this->assertJsonContains([
            'message' => 'Login Successfull.',
            'username' => 'Rey',
        ]);

        // Verify a token is actually returned
        $data = $response->toArray();
        $this->assertArrayHasKey('token', $data);
    }

    public function testLoginInvalidPassword(): void
    {
        static::createClient()->request('POST', '/api/login', [
            'json' => [
                'username' => 'Rey',
                'password' => 'nald',
            ],
        ]);

        // Matches the 404 status code you set for invalid passwords
        $this->assertResponseStatusCodeSame(404);
        $this->assertJsonContains([
            'message' => 'Invalid Password, please try again.',
        ]);
    }
}

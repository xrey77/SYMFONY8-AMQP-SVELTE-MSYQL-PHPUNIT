<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\User;
use App\Message\UserMessage;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

class RegisterTest extends ApiTestCase
{

    use InteractsWithMessenger; 
    protected static ?bool $alwaysBootKernel = true;

    public function testRegisterSuccess(): void
    {

        $client = static::createClient();
        
        $client->request('POST', '/api/register', [
            'json' => [
                'firstname' => 'Manny',
                'lastname' => 'Pacquiao',
                'email' => 'manny@yahoo.com',
                'mobile' => '2342423',
                'username' => 'Manny',
                'password' => 'rey',
                'roles' => ["ROLE_USER"]
            ],
        ]);

        $this->assertResponseStatusCodeSame(201); 
        
        $this->messenger('async_users')->queue()->assertContains(UserMessage::class);   
        $this->messenger('async_users')->queue()->assertCount(1);
    }
}

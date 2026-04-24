<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\User;
use App\Message\UserMessage;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

class ActivatemfaTest extends ApiTestCase
{
    use InteractsWithMessenger;
    protected static ?bool $alwaysBootKernel = true;

    public function testActivatemfaSuccess(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $em = $container->get('doctrine')->getManager();
        $hasher = $container->get(UserPasswordHasherInterface::class);

        // 1. Create and persist a test user
        $user = new User();
        $user->setEmail('nora@yahoo.com');
        $user->setFirstname('Nora');
        $user->setLastname('Aunor');
        $user->setMobile('3234242');
        $user->setUsername('Nora');        
        $user->setPassword($hasher->hashPassword($user, 'rey'));
        $user->setRoles(["ROLE_USER"]);
        $user->setIsactivated(1);
        $user->setIsblocked(0);
        $user->setMailtoken(0);
        
        $em->persist($user);
        $em->flush();

        // 2. Log in as that user
        $client->loginUser($user);

        // 1. Clear the messenger queue before the request to ensure a clean state
        $this->messenger('async_users')->queue()->assertEmpty();


        $client->request('PATCH', "/api/activationmfa/{$user->getId()}", [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
                'Accept' => 'application/ld+json',
            ],
            'json' => [
                'Twofactorenabled' => true
            ] 
        ]);

        // 4. Assert 200 OK
        $this->assertResponseStatusCodeSame(200); 
        $this->messenger('async_users')->queue()->assertContains(UserMessage::class);   
        $this->messenger('async_users')->queue()->assertCount(1);
    }
}

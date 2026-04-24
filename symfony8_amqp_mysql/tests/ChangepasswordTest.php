<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\User;
use App\Message\UserMessage;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

class ChangepasswordTest extends ApiTestCase
{
    use InteractsWithMessenger;

    public function testChangepasswordSuccess(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $em = $container->get('doctrine')->getManager();
        $hasher = $container->get(UserPasswordHasherInterface::class);

        // 1. Create and persist a test user
        $user = new User();
        $user->setEmail('violeta@yahoo.com');
        $user->setFirstname('Violeta');
        $user->setLastname('Domingo');
        $user->setMobile('3234242');
        $user->setUsername('Violeta');
        
        // Use a simple plain password for initial creation
        $user->setPassword($hasher->hashPassword($user, 'rey'));
        $user->setRoles(["ROLE_USER"]);
        $user->setIsactivated(1);
        $user->setIsblocked(0);
        $user->setMailtoken(0);
        
        $em->persist($user);
        $em->flush();

        // 2. Log in as that user
        $client->loginUser($user);

        // 3. Send a PATCH request with a PLAIN password
        // API Platform state processors typically expect 'password' or 'plainPassword' 
        // to be hashed automatically on the server side.
        $client->request('PATCH', "/api/updateprofile/{$user->getId()}", [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
                'Accept' => 'application/ld+json',
            ],
            'json' => [
                'password' => 'nald' 
            ] 
        ]);

        // 4. Assert 200 OK
        $this->assertResponseStatusCodeSame(200); 
        
        // 5. Verify the Messenger queue using Zenstruck helpers
        // Ensure 'async_users' matches your transport name in messenger.yaml
        $this->messenger('async_users')->queue()->assertContains(UserMessage::class);   
        $this->messenger('async_users')->queue()->assertCount(1);
    }
}

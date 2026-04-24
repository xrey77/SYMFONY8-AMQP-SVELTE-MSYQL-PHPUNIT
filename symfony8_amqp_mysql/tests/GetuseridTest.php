<?php

namespace App\Tests;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\User;
use App\Message\UserMessage;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

class GetuseridTest extends ApiTestCase
{
    use InteractsWithMessenger; 

    public function testGetusridSuccess(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $em = $container->get('doctrine')->getManager();
        $hasher = $container->get(UserPasswordHasherInterface::class);

        // 1. Create and persist a test user
        $user = new User();
        $user->setFirstname('Bianca');
        $user->setLastname('Umali');
        $user->setEmail('bianca@yahoo.com');
        $user->setMobile('3234242');
        $user->setUsername('Bianca');

        $hashedPassword = $hasher->hashPassword($user, 'rey');
        $user->setPassword($hashedPassword);
        $user->setRoles(["ROLE_USER"]);
        $user->setIsactivated(1);
        $user->setIsblocked(0);
        $user->setMailtoken(0);
        
        $em->persist($user);
        $em->flush();

        // // 2. Log in as that user to pass #[IsGranted]
        $client->loginUser($user);

        // 3. Request the ID of the user we just created
        $client->request('GET', "/api/getuserid/{$user->getId()}");

        // 4. Assert 200 (Controller returns 200, not 201)
        $this->assertResponseStatusCodeSame(200); 
        
        // 5. Verify the Messenger queue
        $this->messenger('async_users')->queue()->assertContains(UserMessage::class);   
        $this->messenger('async_users')->queue()->assertCount(1);
    }
}
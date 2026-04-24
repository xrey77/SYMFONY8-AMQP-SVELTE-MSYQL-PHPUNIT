<?php

namespace App\Tests;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\User;
use App\Message\UserMessage;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

class MfaverificationTest extends ApiTestCase
{
    use InteractsWithMessenger; 

    public function testMfaverificationSuccess(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $em = $container->get('doctrine')->getManager();
        $hasher = $container->get(UserPasswordHasherInterface::class);

        // 1. Create and persist a test user
        $user = new User();
        $user->setFirstname('Boby');
        $user->setLastname('Vargas');
        $user->setEmail('bobby@yahoo.com');
        $user->setMobile('3234242');
        $user->setUsername('Bobby');
        $token = $container->get('lexik_jwt_authentication.jwt_manager')->create($user);

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

        $client->request('PATCH', "/api/activationmfa/{$user->getId()}", [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
                'Accept' => 'application/ld+json',
                'Authorization' => 'Bearer ' . $token
            ],
            'json' => [
                'Twofactorenabled' => true
            ] 
        ]);


        // 3. Request the ID of the user we just created
        $client->request('PATCH', "/api/otpvalidation/{$user->getId()}", [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
                'Authorization' => 'Bearer ' . $token
                ],
            'json' => [
                'otp' => '123456' //enter real OTP
                ] 
        ]);

        // 4. Assert 200 (Controller returns 200, not 201)
        $this->assertResponseStatusCodeSame(200); 
        
        // 5. Verify the Messenger queue
        $this->messenger('async_users')->queue()->assertContains(UserMessage::class);   
        $this->messenger('async_users')->queue()->assertCount(1);
    }
}
<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTExpiredEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTInvalidEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTNotFoundEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;

final class JWTExceptionListener
{

    #[AsEventListener(event: Events::JWT_INVALID, priority: 10)]
    public function onJWTInvalid(JWTInvalidEvent $event): void
    {
        $event->setResponse(new JsonResponse(['message' => 'Bearer Token is not valid.'], 401));
    }


    // #[AsEventListener(event: Events::JWT_INVALID)]
    // public function onJWTInvalid(JWTInvalidEvent $event): void
    // {
    //     $event->setResponse(new JsonResponse(['message' => 'Bearer Token is not valid.'], 401));
    // }

    #[AsEventListener(event: Events::JWT_EXPIRED)]
    public function onJWTExpired(JWTExpiredEvent $event): void
    {
        $event->setResponse(new JsonResponse(['message' => 'Bearer Token is already expired.'], 401));
    }

    #[AsEventListener(event: Events::JWT_NOT_FOUND)]
    public function onJWTNotFound(JWTNotFoundEvent $event): void
    {
        // Note: This only fires if the firewall is hit and no token is present
        $event->setResponse(new JsonResponse(['message' => 'Please enter Bearer Token.'], 401));
    }
}

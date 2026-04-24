<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Messenger\MessageBusInterface;
use App\Message\UserMessage;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Messenger\Exception\ExceptionInterface;


final class GetuseridController extends AbstractController
{
    #[Route('/api/getuserid/{id}', name: 'app_userid', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function getUserId(
        int $id,        
        EntityManagerInterface $em,
        User $user, 
        LoggerInterface $logger,
        MessageBusInterface $bus,
        SerializerInterface $serializer
    ): JsonResponse {

        $this->denyAccessUnlessGranted('VIEW_PROFILE', $user);

        $query = $em->createQuery(
            'SELECT u.id, u.firstname, u.lastname, u.email, u.mobile, u.qrcodeurl, u.roles, u.isactivated, u.isblocked, u.userpic
            FROM App\Entity\User u
            WHERE u.id = :id'
        )->setParameter('id', $id);

        $results = $query->getResult();

        if ($results) {
            $userData = $results[0];

            try {
                // Dispatch async message (AMQP)
                $bus->dispatch(new UserMessage($user->getId(), 'login_success'));
            } catch (ExceptionInterface $e) {
                // Log the error so the API doesn't fail just because the message queue is down
                $logger->error('Could not dispatch UserMessage: ' . $e->getMessage());
            }

            $data = $serializer->serialize([
                'message' => 'User profile retrieved.',
                'user' => $userData
            ], 'json', ['groups' => 'user:read']);            

            return new JsonResponse($data, 200, [], true);

        }

        return new JsonResponse(['message' => 'User ID not found.'], 404);
    }
}
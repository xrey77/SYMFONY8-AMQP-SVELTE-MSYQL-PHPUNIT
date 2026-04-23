<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request; 
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

final class UpdateprofileController extends AbstractController
{
    #[Route('/api/updateprofile/{id}', name: 'app_updateprofile', methods: ['PATCH'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function updateProfile(
        int $id,
        Request $request,
        EntityManagerInterface $em,
        User $user, 
        LoggerInterface $logger,
        MessageBusInterface $bus,
        SerializerInterface $serializer
    ): JsonResponse {

        #Security/Voter/UserVoter.php
        $this->denyAccessUnlessGranted('VIEW_PROFILE', $user);

        $data = json_decode($request->getContent(), true);
        
        if (!$user) {
            return new JsonResponse(['message' => 'User ID not found.'], 404);
        }

        $user->setFirstname($data['firstname'] ?? $user->getFirstname());
        $user->setLastname($data['lastname'] ?? $user->getLastname());
        $user->setMobile($data['mobile'] ?? $user->getMobile());

        $em->flush();            
        
        try {
            // Dispatch async message (AMQP)
            $bus->dispatch(new UserMessage($user->getId(), 'updateprofile_success'));
        } catch (ExceptionInterface $e) {
            // Log the error so the API doesn't fail just because the message queue is down
            $logger->error('Could not dispatch UserMessage: ' . $e->getMessage());
        }

        return new JsonResponse(['message' => 'User Profile has been updated.'], 200);
    }
}

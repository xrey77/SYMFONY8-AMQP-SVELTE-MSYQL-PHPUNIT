<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Psr\Log\LoggerInterface;
use App\Message\UserMessage;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class ChangepasswordController extends AbstractController
{
    #[Route('/api/changeuserpassword/{id}', name: 'app_changeuserpassword', methods: ['PATCH'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function updateUserpassword(
        Request $request,
        User $user, // Symfony automatically fetches User by {id}
        EntityManagerInterface $em, // Fixed typo
        UserPasswordHasherInterface $passwordHasher,
        MessageBusInterface $bus,
        LoggerInterface $logger
    ): JsonResponse {
        
        $this->denyAccessUnlessGranted('VIEW_PROFILE', $user);

        $data = json_decode($request->getContent());
        
        // Ensure password exists in request
        if (!isset($data->password)) {
            return new JsonResponse(['error' => 'Password required'], 400);
        }

        $hashedPassword = $passwordHasher->hashPassword($user, $data->password);
        $user->setPassword($hashedPassword);
        
        $em->flush();

        try {
            // Dispatch async message (AMQP)
            $bus->dispatch(new UserMessage($user->getId(), 'changepassword_success'));
        } catch (ExceptionInterface $e) {
            $logger->error('Could not dispatch UserMessage: ' . $e->getMessage());
            // We don't return 500 here so the user knows their password WAS changed
        }

        return new JsonResponse(['message' => 'Your Password has been changed successfully.'], 200);
    }
}

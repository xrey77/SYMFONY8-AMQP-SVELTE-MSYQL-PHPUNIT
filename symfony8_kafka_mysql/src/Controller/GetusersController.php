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

final class GetusersController extends AbstractController
{
  #[Route('/api/getallusers', name: 'app_users', methods: ['GET'])]
  #[IsGranted('IS_AUTHENTICATED_FULLY')]
  public function getUsers(
    UserRepository $userRepository,
    LoggerInterface $logger,
    EntityManagerInterface $em,
    MessageBusInterface $bus
): JsonResponse {
    
    /** @var User $currentUser */
    $currentUser = $this->getUser();

    $this->denyAccessUnlessGranted('VIEW_PROFILE', $currentUser);

    try {
        $query = $em->createQuery(
            'SELECT u.id, u.firstname, u.lastname, u.email, u.mobile, u.qrcodeurl, u.roles, u.isactivated, u.isblocked, u.userpic
            FROM App\Entity\User u');
        $users = $query->getResult();

        // AMQP Dispatch
        try {
            $bus->dispatch(new UserMessage($currentUser->getId(), 'getusers_success'));
        } catch (ExceptionInterface $e) {
            // This logs the error but lets the request continue
            $logger->error('AMQP Dispatch Error: ' . $e->getMessage());
        }

        return $this->json(['users' => $users], 200, [], ['groups' => ['user:read']]);

    } catch (\Exception $e) {
        $logger->critical($e->getMessage());
        return new JsonResponse(['error' => 'Internal Server Error'], 500);
    }
  }
}
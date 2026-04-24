<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Psr\Log\LoggerInterface;
use App\Message\UserMessage;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class UploadpictureController extends AbstractController
{
    #[Route('/api/uploadpicture', name: 'app_updateuserpic', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function updateUserpic(
        Request $request,
        EntityManagerInterface $em,
        SluggerInterface $slugger,
        UserRepository $userRepository,
        MessageBusInterface $bus,
        LoggerInterface $logger
    ): JsonResponse
    {

        $this->denyAccessUnlessGranted('VIEW_PROFILE', $user);
        if (!$user) {
            return new JsonResponse(['message' => 'User ID not found.'], 404);
        }

        $data = json_decode($request->getContent());

        // $idValue = $request->request->get('id');        
        // $user = $em->getRepository(User::class)->find($idValue);
        // if (!$user) {
        //     return new JsonResponse(['message' => 'User ID not found.'], 404);
        // }
        $uploadedFile = $request->files->get('userpic');

        if ($uploadedFile) {
            $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
            $ext = $uploadedFile->guessExtension();

            $newfile = '00' . $idValue . '.' . $ext;

            $projectDir = $this->getParameter('kernel.project_dir');
            $usersPublicPath = $projectDir . '/public/users/';

            $uploadedFile->move($usersPublicPath, $newfile);
            $user->setUserpic('/users/' . $newfile);
            $em->flush();


            try {
                // Dispatch async message (AMQP)
                $bus->dispatch(new UserMessage($user->getId(), 'uploadpicture_success'));
            } catch (ExceptionInterface $e) {
                $logger->error('Could not dispatch UserMessage: ' . $e->getMessage());
                // We don't return 500 here so the user knows their password WAS changed
            }

            return new JsonResponse(['message' => 'User Profile has been updated.'], 200);
        }        
    }
}

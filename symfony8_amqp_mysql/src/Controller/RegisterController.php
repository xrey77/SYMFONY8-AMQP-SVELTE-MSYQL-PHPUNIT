<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use App\Message\UserMessage;

final class RegisterController extends AbstractController
{

    #[Route('/api/register', name: 'app_register', methods: ['POST'])]
    public function index(
        EntityManagerInterface $em,
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        UserRepository $userRepository,
        MessageBusInterface $bus     
        ): Response
    {
        $data = json_decode($request->getContent());

        $useremail = $userRepository->findOneBy(['email' => $data->email]);
        if ($useremail) {
            return $this->json(['message' => 'Email Address is already taken.'], 404);
        }
        $username = $userRepository->findOneBy(['username' => $data->username]);
        if ($username) {
            return $this->json(['message' => 'Username is already taken.'], 404);
        }
        try {
            $plaintextPassword = $data->password;
            $user = new User();
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $plaintextPassword
            );

            $user->setFirstname($data->firstname);
            $user->setLastname($data->lastname);
            $user->setEmail($data->email);
            $user->setMobile($data->mobile);
            $user->setUsername($data->username);
            $user->setPassword($hashedPassword);
            $user->setUserpic('pix.png');
            $user->setMailtoken(0);
            $user->setIsblocked(0);
            $user->setIsactivated(1);
            $user->setRoles(["ROLE_USER"]);
            $user->setCreatedAtValue();
            $user->setUpdatedAtValue();
            $em->persist($user);
            $em->flush();
    } catch(\Exception $ex) {
        return $this->json(['message' => $ex->getMessage()], 404);
    }

        $bus->dispatch(new UserMessage($user->getId(), 'registration_success'));
        return $this->json(['message' => 'You have registed successfully, please login now.'], 201);
    }


}

<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Psr\Log\LoggerInterface; // You can inject other services too
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UserRepository;
use App\Entity\User;
use DateTimeImmutable;
use Symfony\Component\Messenger\MessageBusInterface;
use App\Message\UserMessage;

final class LoginController extends AbstractController
{
    #[Route('/api/login', name: 'app_login', methods: ['POST'])]
    public function index(
        Request $request,
        UserRepository $userRepository,
        UserAuthenticatorInterface $userAuthenticator,
        UserPasswordHasherInterface $passwordHasher,
        JWTTokenManagerInterface $JWTManager,
        MessageBusInterface $bus     
        ): JsonResponse
    {
        $data = json_decode($request->getContent());
        $username = $userRepository->findOneBy(['username' => $data->username]);
        if ($username) {
            if ($username->getIsactivated()== 0) {
                return new JsonResponse([
                    'message' => 'Your account is not yet activated, please check your email inbox and activate.'
                ],404);    
            } 

            if ($username->getIsblocked() == 1) {
                return new JsonResponse([
                    'message' => 'You account has been blocked.'
                ],404);        
            }

            if (password_verify($data->password,$username->getPassword())) {

                // =====START GENERATE TOKEN=====                    
                $expiration = new DateTimeImmutable('+1 day');
                $customPayload = [
                    'exp' => $expiration->getTimestamp(),
                ];
                $token = $JWTManager->createFromPayload($username, $customPayload);
                // ======END GENERATE TOKEN======
                    $roles = $username->getRoles();
                    $userRole = in_array('ROLE_USER', $roles) ? 'ROLE_ADMIN' : null;
                    
                    $bus->dispatch(new UserMessage($username->getId(), 'login_success'));

                    return new JsonResponse([
                        'message' => 'Login Successfull.',
                        'id' => $username->getId(),
                        'fullname' => $username->getFirstname() . ' ' . $username->getLastname() ,
                        'username' => $username->getUsername(),
                        'roles' => $roles[0],
                        'email' => $username->getEmail(),
                        'isactivated' => $username->getIsactivated(),
                        'isblocked' => $username->getIsblocked(),
                        'userpicture' => $username->getUserpic(),
                        'secretkey' => $username->getSecretkey(),
                        'qrcodeurl' => $username->getQrcodeurl(),
                        'token' => $token //$JWTManager->create($username)
                    ],200);
            } else {

                return new JsonResponse([
                    'message' => 'Invalid Password, please try again.'
                ],404);    

            }
        } else {
            return new JsonResponse([
                'message' => 'Username does not exists, please register.'
            ],404);
        }
    }
}

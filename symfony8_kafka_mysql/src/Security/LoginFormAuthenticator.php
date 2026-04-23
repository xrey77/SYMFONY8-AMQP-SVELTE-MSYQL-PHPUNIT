<?php

// src/Security/LoginFormAuthenticator.php

// ... (existing code from make:security:form-login)

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
// use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;

use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\PasswordCredentialsBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use App\Repository\UserRepository;


class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    // ... (constructor and LOGIN_ROUTE constant)
    private RouterInterface $router;
    private UserRepository $userRepository;

    public function __construct(
        RouterInterface $_router,
        UserRepository $_userRepository        
        )
    {
        $this->router = $_router;
        $this->userRepository = $_userRepository;
    }

    // ... other required methods (e.g., authenticate, onAuthenticationSuccess)

    protected function getLoginUrl(Request $request): string
    {
        // Replace 'app_login' with the actual route name of your login page
        return $this->router->generate('app_login');
    }


    public function authenticate(Request $request): Passport //PassportInterface
    {
        // The json_login listener handles reading from the JSON payload. 
        // This method is primarily for generating the Passport.
        // If you're using json_login, the listener handles the request parsing.
        // However, if you wanted to manually get data:
        $data = json_decode($request->getContent(), true);
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';

        $request->getSession()->set(\Symfony\Component\Security\Http\Security::LAST_USERNAME, $username);


        return new Passport(
            new UserBadge($username, function (string $userIdentifier): ?UserInterface {
                return $this->userRepository->findOneBy(['username' => $userIdentifier]);
            }),
            $credentials
        );



        // return new Passport(
        //     new UserBadge($username),
        //     new PasswordCredentialsBadge($password),
        //     [
        //         // new CsrfTokenBadge('authenticate', $request->get('_csrf_token')), // CSRF might not be needed for SPA API login if using other methods like JWT
        //     ]
        // );
    }

    public function onAuthenticationSuccess(
        Request $request, 
        TokenInterface $token, 
        string $firewallName): ?Response
    {

        $user = $token->getUser();

        // Check if the user is an instance of your User entity
        if (!$user instanceof User) {
            return new JsonResponse(['message' => 'Authentication successful, but user data not available.'], Response::HTTP_OK);
        }
    
        $userData = $this->serializer->serialize(
            $user,
            'json',
            ['groups' => ['user:read']] // Define a serialization group in your User entity for security
        );

        return new JsonResponse($userData, Response::HTTP_OK, [], true);

        // return new JsonResponse([
        //     'message' => 'Login successful', 
        //     'user' => $token->getUser()->getUserIdentifier()],200);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        // Return a JSON error response for an SPA
        $data = [
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData())
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }
    
    // ... (getLoginUrl method)
}

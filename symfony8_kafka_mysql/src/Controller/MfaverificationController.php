<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Builder\BuilderInterface;
use Endroid\QrCodeBundle\Response\QrCodeResponse;
use OTPHP\TOTP;
use Psr\Clock\ClockInterface;
use Scheb\TwoFactorBundle\Security\Authentication\Token\TwoFactorTokenInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderRegistry;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface ;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Repository\UserRepository;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Scheb\TwoFactorBundle\Model\Totp\TwoFactorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Message\UserMessage;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class MfaverificationController extends AbstractController
{
    #[Route('/api/otpvalidation/{id}', name: 'app_verify_2fa_manual', methods: ['PATCH'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function validateOtp(
        User $user,
        Request $request,
        MessageBusInterface $bus,
        LoggerInterface $logger
    ): JsonResponse {

        $this->denyAccessUnlessGranted('VIEW_PROFILE', $user);
        if (!$user) {
            return new JsonResponse(['message' => 'User ID not found.'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $otp = $data['otp'] ?? null;

        if (!$otp) {
            return new JsonResponse(['message' => 'OTP code is required.'], 400);
        }

        $secret = $user->getTotpSecret();
        if (!$secret) {
            return new JsonResponse(['message' => '2FA secret not found for user.'], 400);
        }

        try {

            try {
                // Dispatch async message (AMQP)
                $bus->dispatch(new UserMessage($user->getId(), 'activatemfa_success'));
            } catch (ExceptionInterface $e) {
                $logger->error('Could not dispatch UserMessage: ' . $e->getMessage());
                // We don't return 500 here so the user knows their password WAS changed
            }

            $totp = TOTP::create($secret);
            if ($totp->verify($otp)) {
                return new JsonResponse([
                    'message' => 'Successful OTP code validation.', 
                    'username' => $user->getUserIdentifier()
                ], 200);
            }
            
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Verification failed.'], 500);
        }

        return new JsonResponse(['message' => 'OTP Code is not valid.'], 401); // 401 is more appropriate than 404 for invalid codes
    }


    // #[Route('/api/otpvalidation/{id}', name: 'app_verify_2fa_manual', methods: ['PATCH'])]
    // #[IsGranted('IS_AUTHENTICATED_FULLY')]
    // public function validateOtp(
    //     int $id,
    //     Request $request,
    //     EntityManagerInterface $em
    //     ): JsonResponse
    // {

    //     $data = json_decode($request->getContent(), true);
    //     if (!$data || !isset($data['otp'])) {
    //         return new JsonResponse(['message' => 'Invalid JSON or missing OTP field.'], 400);
    //     }


    //     $otp = $data['otp'] ?? null;

    //     $user = $em->getRepository(User::class)->find($id);
    //     if (!$user) {
    //         return new JsonResponse(['message' => 'User ID not found.'], 404);
    //     }

    //     $totp = TOTP::create($user->getTotpSecret());
    //     if ($totp->verify($otp)) {
    //         return new JsonResponse([
    //             'message' => 'Successful OTP code validation.', 
    //             'username' => $user->getUserIdentifier()
    //         ], 200);
    //     }

    //     return new JsonResponse(['message' => 'OTP Code is not valid.'], 404);
    // }
}

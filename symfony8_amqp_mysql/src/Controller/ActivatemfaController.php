<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Doctrine\ORM\EntityManagerInterface;
use Endroid\QrCode\Builder\BuilderInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Psr\Log\LoggerInterface;
use App\Message\UserMessage;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;


final class ActivatemfaController extends AbstractController
{
    private TotpAuthenticatorInterface $totpAuthenticator;
    private BuilderInterface $qrCodeBuilder;

    public function __construct(
        TotpAuthenticatorInterface $totpAuthenticator,
        BuilderInterface $qrCodeBuilder
    ) {
        $this->totpAuthenticator = $totpAuthenticator;
        $this->qrCodeBuilder = $qrCodeBuilder;
    }

    #[Route('/api/activationmfa/{id}', name: 'generate_qrcode_base64', methods: ['PATCH'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function activateMfa(
        User $user,
        Request $request,
        EntityManagerInterface $em,
        MessageBusInterface $bus,
        LoggerInterface $logger
    ): JsonResponse {

        $this->denyAccessUnlessGranted('VIEW_PROFILE', $user);

        if (!$user) {
            return new JsonResponse(['message' => 'User ID not found.'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $isEnabled = $data['Twofactorenabled'] ?? false;

        if ($isEnabled === false) {
            $user->setTotpSecret(null);
            // Assuming your User entity has this field for the Base64 string
            $user->setQrcodeurl(null); 
            $em->flush();

            return new JsonResponse(['message' => 'Multi-Factor Authenticator has been Disabled.'], 200);
        }

        // 1. Generate Secret using Scheb's Authenticator
        $secret = $this->totpAuthenticator->generateSecret();
        $user->setTotpSecret($secret);
        
        // 2. Generate the QR Content (the URI)
        // Scheb provides a helper to get the QR code content (the otpauth:// URL)
        $qrCodeContent = $this->totpAuthenticator->getQRContent($user);

        // 3. Build the QR Image as Base64
        $result = $this->qrCodeBuilder->build(
            data: $qrCodeContent,
            size: 200,
            margin: 10
        );
        
        $dataUri = $result->getDataUri();
        $user->setQrcodeurl($dataUri);        
        $em->flush();

        try {
            // Dispatch async message (AMQP)
            $bus->dispatch(new UserMessage($user->getId(), 'activatemfa_success'));
        } catch (ExceptionInterface $e) {
            $logger->error('Could not dispatch UserMessage: ' . $e->getMessage());
            // We don't return 500 here so the user knows their password WAS changed
        }

        return new JsonResponse([
            'message' => 'Multi-Factor Authenticator has been Enabled.',
            'qrcodeurl' => $dataUri
        ], 200);
    }
}

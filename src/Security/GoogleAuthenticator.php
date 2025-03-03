<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\Provider\GoogleClient;
use League\OAuth2\Client\Provider\GoogleUser;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractOAuth2Authenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\NoCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

class GoogleAuthenticator extends AbstractOAuth2Authenticator
{
    private GoogleClient $client;
    private EntityManagerInterface $entityManager;
    private RouterInterface $router;

    public function __construct(GoogleClient $client, EntityManagerInterface $entityManager, RouterInterface $router)
    {
        $this->client = $client;
        $this->entityManager = $entityManager;
        $this->router = $router;
    }

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === 'connect_google_check';
    }

    public function authenticate(Request $request): Passport
    {
        $googleUser = $this->client->fetchUser();

        return new Passport(
            new UserBadge($googleUser->getEmail(), function () use ($googleUser) {
                $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $googleUser->getEmail()]);

                if (!$user) {
                    throw new CustomUserMessageAuthenticationException('Aucun compte trouvé pour cet email.');
                }

                return $user;
            }),
            new NoCredentials()
        );
    }

    public function onAuthenticationSuccess(Request $request, UserInterface $user, string $firewallName): RedirectResponse
    {
        return new RedirectResponse($this->router->generate('app_home'));
    }
}

<?php declare(strict_types=1);

namespace danielburger1337\SchebTwoFactorBundle\TwoFactorProvider;

use danielburger1337\SchebTwoFactorBundle\AuthCodeProvider\AuthCodeProviderInterface;
use danielburger1337\SchebTwoFactorBundle\Model\TwoFactorEmailInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContextInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorFormRendererInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderInterface;
use Symfony\Component\Clock\ClockInterface;

final class TwoFactorEmailProvider implements TwoFactorProviderInterface
{
    public function __construct(
        private readonly AuthCodeProviderInterface $authCodeProvider,
        private readonly TwoFactorFormRendererInterface $formRenderer,
        private readonly ClockInterface $clock,
    ) {
    }

    public function beginAuthentication(AuthenticationContextInterface $context): bool
    {
        $user = $context->getUser();

        return $user instanceof TwoFactorEmailInterface && $user->isEmailAuthEnabled();
    }

    public function prepareAuthentication(object $user): void
    {
        if (!$user instanceof TwoFactorEmailInterface) {
            return;
        }

        $this->authCodeProvider->createAuthCode($user);
    }

    public function validateAuthenticationCode(object $user, string $authenticationCode): bool
    {
        if (!$user instanceof TwoFactorEmailInterface) {
            return false;
        }

        $expiresAt = $user->getEmailAuthCodeExpiresAt();
        if (null !== $expiresAt && $this->clock->now()->getTimestamp() >= $expiresAt->getTimestamp()) {
            $this->authCodeProvider->createAuthCode($user);

            return false;
        }

        if ($user->getEmailAuthCode() !== \str_replace(' ', '', $authenticationCode)) {
            return false;
        }

        return true;
    }

    public function getFormRenderer(): TwoFactorFormRendererInterface
    {
        return $this->formRenderer;
    }
}

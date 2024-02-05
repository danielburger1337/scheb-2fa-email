<?php declare(strict_types=1);

namespace danielburger1337\SchebTwoFactorBundle\AuthCodeProvider;

use danielburger1337\SchebTwoFactorBundle\Mailer\AuthCodeMailerInterface;
use danielburger1337\SchebTwoFactorBundle\Model\TwoFactorEmailInterface;
use Scheb\TwoFactorBundle\Model\PersisterInterface;
use Symfony\Component\Clock\ClockInterface;

final class AuthCodeProvider implements AuthCodeProviderInterface
{
    public function __construct(
        private readonly PersisterInterface $persister,
        private readonly AuthCodeGeneratorInterface $authCodeGenerator,
        private readonly AuthCodeMailerInterface $mailer,
        private readonly ClockInterface $clock,
        private readonly string|null $expiresAfter = null,
    ) {
    }

    public function createAuthCode(TwoFactorEmailInterface $user): void
    {
        $user->setEmailAuthCode($this->authCodeGenerator->generateAuthCode($user));

        if (null !== $this->expiresAfter) {
            $user->setEmailAuthCodeExpiresAt($this->clock->now()->add(new \DateInterval($this->expiresAfter)));
        }

        $this->persister->persist($user);
        $this->mailer->sendAuthCode($user);
    }
}

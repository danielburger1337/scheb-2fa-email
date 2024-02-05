<?php declare(strict_types=1);

namespace danielburger1337\SchebTwoFactorBundle\Mailer;

use danielburger1337\SchebTwoFactorBundle\Model\TwoFactorEmailInterface;
use Symfony\Component\Mailer\MailerInterface;

final class SymfonyAuthCodeMailer implements AuthCodeMailerInterface
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly AuthCodeEmailGeneratorInterface $authCodeEmailGenerator
    ) {
    }

    public function sendAuthCode(TwoFactorEmailInterface $user): void
    {
        if (null === $user->getEmailAuthCode()) {
            return;
        }

        $message = $this->authCodeEmailGenerator->createAuthCodeEmail($user);

        $this->mailer->send($message);
    }
}

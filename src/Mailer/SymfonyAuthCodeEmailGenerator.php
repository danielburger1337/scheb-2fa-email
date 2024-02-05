<?php declare(strict_types=1);

namespace danielburger1337\SchebTwoFactorBundle\Mailer;

use danielburger1337\SchebTwoFactorBundle\Model\TwoFactorEmailInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

final class SymfonyAuthCodeEmailGenerator implements AuthCodeEmailGeneratorInterface
{
    private Address|string|null $senderAddress;

    public function __construct(
        private readonly string $subject,
        private readonly string $textBody,
        string|null $senderEmail,
        ?string $senderName = null,
    ) {
        if (null !== $senderEmail && null !== $senderName) {
            $this->senderAddress = new Address($senderEmail, $senderName);
        } elseif (null !== $senderEmail) {
            $this->senderAddress = $senderEmail;
        }
    }

    public function createAuthCodeEmail(TwoFactorEmailInterface $user): Email
    {
        $authCode = $user->getEmailAuthCode();
        if (null === $authCode) {
            throw new \InvalidArgumentException();
        }

        $message = new Email();
        $message
            ->to($user->getEmailAuthRecipient())
            ->subject($this->subject)
            ->text(\str_replace('{{AUTH_CODE}}', $authCode, $this->textBody))
        ;

        if (null !== $this->senderAddress) {
            $message->from($this->senderAddress);
        }

        return $message;
    }
}

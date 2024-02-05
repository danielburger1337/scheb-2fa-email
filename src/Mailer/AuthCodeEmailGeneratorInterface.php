<?php declare(strict_types=1);

namespace danielburger1337\SchebTwoFactorBundle\Mailer;

use danielburger1337\SchebTwoFactorBundle\Model\TwoFactorEmailInterface;
use Symfony\Component\Mime\Email;

interface AuthCodeEmailGeneratorInterface
{
    /**
     * Create the authentication code email message.
     *
     * @param TwoFactorEmailInterface $user The user to create the auth code message for.
     */
    public function createAuthCodeEmail(TwoFactorEmailInterface $user): Email;
}

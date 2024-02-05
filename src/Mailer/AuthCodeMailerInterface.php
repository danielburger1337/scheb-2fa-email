<?php declare(strict_types=1);

namespace danielburger1337\SchebTwoFactorBundle\Mailer;

use danielburger1337\SchebTwoFactorBundle\Model\TwoFactorEmailInterface;

interface AuthCodeMailerInterface
{
    /**
     * Send the auth code to the user.
     *
     * @param TwoFactorEmailInterface $user The user to send the auth code to.
     */
    public function sendAuthCode(TwoFactorEmailInterface $user): void;
}

<?php declare(strict_types=1);

namespace danielburger1337\SchebTwoFactorBundle\AuthCodeProvider;

use danielburger1337\SchebTwoFactorBundle\Model\TwoFactorEmailInterface;

interface AuthCodeProviderInterface
{
    /**
     * Create and send a new authentication code.
     *
     * @param TwoFactorEmailInterface $user The user to create to auth code for.
     */
    public function createAuthCode(TwoFactorEmailInterface $user): void;
}

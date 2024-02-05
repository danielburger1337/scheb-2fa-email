<?php declare(strict_types=1);

namespace danielburger1337\SchebTwoFactorBundle\AuthCodeProvider;

use danielburger1337\SchebTwoFactorBundle\Model\TwoFactorEmailInterface;

interface AuthCodeGeneratorInterface
{
    /**
     * Generate a new authentication code.
     *
     * @param TwoFactorEmailInterface $user The user the code is being generated for.
     */
    public function generateAuthCode(TwoFactorEmailInterface $user): string;
}

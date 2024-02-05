<?php declare(strict_types=1);

namespace danielburger1337\SchebTwoFactorBundle\AuthCodeProvider;

use danielburger1337\SchebTwoFactorBundle\Model\TwoFactorEmailInterface;

final class AuthCodeGenerator implements AuthCodeGeneratorInterface
{
    public function __construct(
        private readonly int $digits
    ) {
    }

    public function generateAuthCode(TwoFactorEmailInterface $user): string
    {
        $min = 10 ** ($this->digits - 1);
        $max = 10 ** $this->digits - 1;

        return (string) \random_int($min, $max);
    }
}

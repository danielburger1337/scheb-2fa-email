<?php declare(strict_types=1);

namespace danielburger1337\SchebTwoFactorBundle\Tests\AuthCodeProvider;

use danielburger1337\SchebTwoFactorBundle\AuthCodeProvider\AuthCodeGeneratorInterface;
use danielburger1337\SchebTwoFactorBundle\Model\TwoFactorEmailInterface;

class MockAuthCodeGenerator implements AuthCodeGeneratorInterface
{
    public function __construct(
        private readonly string $authCode,
    ) {
    }

    public function generateAuthCode(TwoFactorEmailInterface $user): string
    {
        return $this->authCode;
    }
}

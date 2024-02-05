<?php declare(strict_types=1);

namespace danielburger1337\SchebTwoFactorBundle\Tests\AuthCodeProvider;

use danielburger1337\SchebTwoFactorBundle\AuthCodeProvider\AuthCodeGenerator;
use danielburger1337\SchebTwoFactorBundle\Model\TwoFactorEmailInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class AuthCodeGeneratorTest extends TestCase
{
    private const DIGITS = 6;

    #[Test]
    public function testAuthCodeExpectedLength(): void
    {
        $user = $this->createMock(TwoFactorEmailInterface::class);

        $authCodeGenerator = new AuthCodeGenerator(self::DIGITS);
        $authCode = $authCodeGenerator->generateAuthCode($user);

        $this->assertEquals(self::DIGITS, \strlen($authCode));
    }

    #[Test]
    public function testAuthCodeIsRandom(): void
    {
        $user = $this->createMock(TwoFactorEmailInterface::class);

        $authCodeGenerator = new AuthCodeGenerator(self::DIGITS);
        $value1 = $authCodeGenerator->generateAuthCode($user);
        $value2 = $authCodeGenerator->generateAuthCode($user);

        $this->assertNotEquals($value1, $value2);
    }
}

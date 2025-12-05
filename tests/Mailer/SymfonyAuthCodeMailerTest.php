<?php declare(strict_types=1);

namespace danielburger1337\SchebTwoFactorBundle\Tests\Mailer;

use danielburger1337\SchebTwoFactorBundle\Mailer\AuthCodeEmailGeneratorInterface;
use danielburger1337\SchebTwoFactorBundle\Mailer\SymfonyAuthCodeMailer;
use danielburger1337\SchebTwoFactorBundle\Model\TwoFactorEmailInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class SymfonyAuthCodeMailerTest extends TestCase
{
    #[Test]
    public function testThatEmailIsSent(): void
    {
        $user = $this->createMock(TwoFactorEmailInterface::class);
        $user->expects($this->once())
            ->method('getEmailAuthCode')
            ->willReturn('123456');

        $emailGenerator = $this->createMock(AuthCodeEmailGeneratorInterface::class);
        $emailGenerator->expects($this->once())
            ->method('createAuthCodeEmail')
            ->with($user)
            ->willReturn($this->createStub(Email::class));

        $symfonyMailer = $this->createMock(MailerInterface::class);
        $symfonyMailer->expects($this->once())
            ->method('send');

        $mailer = new SymfonyAuthCodeMailer($symfonyMailer, $emailGenerator);
        $mailer->sendAuthCode($user);
    }

    #[Test]
    public function testThatNullAuthCodeSendsNoEmail(): void
    {
        $user = $this->createMock(TwoFactorEmailInterface::class);
        $user->expects($this->atLeastOnce())
            ->method('getEmailAuthCode')
            ->willReturn(null);

        $emailGenerator = $this->createMock(AuthCodeEmailGeneratorInterface::class);
        $emailGenerator->expects($this->never())
            ->method('createAuthCodeEmail');

        $symfonyMailer = $this->createMock(MailerInterface::class);
        $symfonyMailer->expects($this->never())
            ->method('send');

        $mailer = new SymfonyAuthCodeMailer($symfonyMailer, $emailGenerator);
        $mailer->sendAuthCode($user);
    }
}

<?php declare(strict_types=1);

namespace danielburger1337\SchebTwoFactorBundle\Tests\Mailer;

use danielburger1337\SchebTwoFactorBundle\Mailer\AuthCodeEmailGeneratorInterface;
use danielburger1337\SchebTwoFactorBundle\Mailer\SymfonyAuthCodeMailer;
use danielburger1337\SchebTwoFactorBundle\Model\TwoFactorEmailInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class SymfonyAuthCodeMailerTest extends TestCase
{
    private MockObject|MailerInterface $symfonyMailer;
    private MockObject|AuthCodeEmailGeneratorInterface $emailGenerator;
    private SymfonyAuthCodeMailer $mailer;

    protected function setUp(): void
    {
        $this->symfonyMailer = $this->createMock(MailerInterface::class);
        $this->emailGenerator = $this->createMock(AuthCodeEmailGeneratorInterface::class);

        $this->mailer = new SymfonyAuthCodeMailer($this->symfonyMailer, $this->emailGenerator);
    }

    #[Test]
    public function testThatEmailIsSent(): void
    {
        $user = $this->createMock(TwoFactorEmailInterface::class);

        $user
            ->expects($this->once())
            ->method('getEmailAuthCode')
            ->willReturn('123456')
        ;

        $this->emailGenerator
            ->expects($this->once())
            ->method('createAuthCodeEmail')
            ->with($user)
            ->willReturn($this->createMock(Email::class))
        ;

        $this->symfonyMailer
            ->expects($this->once())
            ->method('send')
        ;

        $this->mailer->sendAuthCode($user);
    }

    #[Test]
    public function testThatNullAuthCodeSendsNoEmail(): void
    {
        $user = $this->createMock(TwoFactorEmailInterface::class);

        $user
            ->expects($this->any())
            ->method('getEmailAuthCode')
            ->willReturn(null)
        ;

        $this->emailGenerator
            ->expects($this->never())
            ->method('createAuthCodeEmail')
        ;

        $this->symfonyMailer
            ->expects($this->never())
            ->method('send')
        ;

        $this->mailer->sendAuthCode($user);
    }
}

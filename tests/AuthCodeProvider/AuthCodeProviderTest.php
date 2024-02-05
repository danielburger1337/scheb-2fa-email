<?php declare(strict_types=1);

namespace danielburger1337\SchebTwoFactorBundle\Tests\AuthCodeProvider;

use danielburger1337\SchebTwoFactorBundle\AuthCodeProvider\AuthCodeProvider;
use danielburger1337\SchebTwoFactorBundle\Mailer\AuthCodeMailerInterface;
use danielburger1337\SchebTwoFactorBundle\Model\TwoFactorEmailInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Scheb\TwoFactorBundle\Model\PersisterInterface;
use Symfony\Component\Clock\MockClock;

class AuthCodeProviderTest extends TestCase
{
    private const AUTH_CODE = '123456';

    private MockAuthCodeGenerator $authCodeGenerator;
    private MockObject|PersisterInterface $persister;
    private MockObject|AuthCodeMailerInterface $mailer;
    private MockClock $clock;

    private AuthCodeProvider $authCodeProvider;

    protected function setUp(): void
    {
        $this->persister = $this->createMock(PersisterInterface::class);
        $this->mailer = $this->createMock(AuthCodeMailerInterface::class);
        $this->clock = new MockClock();
        $this->authCodeGenerator = new MockAuthCodeGenerator(self::AUTH_CODE);

        $this->authCodeProvider = new AuthCodeProvider($this->persister, $this->authCodeGenerator, $this->mailer, $this->clock);
    }

    #[Test]
    public function testThatAuthCodeIsSetOnUser(): void
    {
        $user = $this->createMock(TwoFactorEmailInterface::class);
        $user->expects($this->once())
            ->method('setEmailAuthCode')
            ->with(self::AUTH_CODE)
        ;

        $this->authCodeProvider->createAuthCode($user);
    }

    #[Test]
    public function testThatAuthCodeIsPersisted(): void
    {
        $user = $this->createMock(TwoFactorEmailInterface::class);

        $this->persister
            ->expects($this->once())
            ->method('persist')
            ->with($user)
        ;

        $this->authCodeProvider->createAuthCode($user);
    }

    #[Test]
    public function testThatAuthCodeIsSendViaMail(): void
    {
        $user = $this->createMock(TwoFactorEmailInterface::class);

        $this->mailer
            ->expects($this->once())
            ->method('sendAuthCode')
            ->with($user)
        ;

        $this->authCodeProvider->createAuthCode($user);
    }

    #[Test]
    public function testThatNoExpirationIsSetOnUser(): void
    {
        $user = $this->createMock(TwoFactorEmailInterface::class);
        $user->expects($this->never())
            ->method('setEmailAuthCodeExpiresAt')
        ;

        $this->authCodeProvider->createAuthCode($user);
    }

    #[Test]
    public function testThatExpirationIsSetOnUser(): void
    {
        $user = $this->createMock(TwoFactorEmailInterface::class);
        $user->expects($this->once())
            ->method('setEmailAuthCodeExpiresAt')
            ->with($this->clock->now()->add(new \DateInterval('PT5M')))
        ;

        $authCodeProvider = new AuthCodeProvider($this->persister, $this->authCodeGenerator, $this->mailer, $this->clock, 'PT5M');
        $authCodeProvider->createAuthCode($user);
    }
}

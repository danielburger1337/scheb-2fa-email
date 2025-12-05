<?php declare(strict_types=1);

namespace danielburger1337\SchebTwoFactorBundle\Tests\AuthCodeProvider;

use danielburger1337\SchebTwoFactorBundle\AuthCodeProvider\AuthCodeProvider;
use danielburger1337\SchebTwoFactorBundle\Mailer\AuthCodeMailerInterface;
use danielburger1337\SchebTwoFactorBundle\Model\TwoFactorEmailInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Scheb\TwoFactorBundle\Model\PersisterInterface;
use Symfony\Component\Clock\MockClock;

class AuthCodeProviderTest extends TestCase
{
    private const string AUTH_CODE = '123456';

    private MockAuthCodeGenerator $authCodeGenerator;
    private MockClock $clock;

    protected function setUp(): void
    {
        $this->clock = new MockClock();
        $this->authCodeGenerator = new MockAuthCodeGenerator(self::AUTH_CODE);
    }

    #[Test]
    public function testThatAuthCodeIsSetOnUser(): void
    {
        $user = $this->createMock(TwoFactorEmailInterface::class);
        $user->expects($this->once())
            ->method('setEmailAuthCode')
            ->with(self::AUTH_CODE)
        ;

        $authCodeProvider = new AuthCodeProvider(
            $this->createStub(PersisterInterface::class),
            $this->authCodeGenerator,
            $this->createStub(AuthCodeMailerInterface::class),
            $this->clock
        );

        $authCodeProvider->createAuthCode($user);
    }

    #[Test]
    public function testThatAuthCodeIsPersisted(): void
    {
        $user = $this->createStub(TwoFactorEmailInterface::class);

        $persister = $this->createMock(PersisterInterface::class);
        $persister->expects($this->once())
            ->method('persist')
            ->with($user);

        $authCodeProvider = new AuthCodeProvider(
            $persister,
            $this->authCodeGenerator,
            $this->createStub(AuthCodeMailerInterface::class),
            $this->clock
        );

        $authCodeProvider->createAuthCode($user);
    }

    #[Test]
    public function testThatAuthCodeIsSendViaMail(): void
    {
        $user = $this->createStub(TwoFactorEmailInterface::class);

        $mailer = $this->createMock(AuthCodeMailerInterface::class);
        $mailer->expects($this->once())
            ->method('sendAuthCode')
            ->with($user);

        $authCodeProvider = new AuthCodeProvider(
            $this->createStub(PersisterInterface::class),
            $this->authCodeGenerator,
            $mailer,
            $this->clock
        );

        $authCodeProvider->createAuthCode($user);
    }

    #[Test]
    public function testThatNoExpirationIsSetOnUser(): void
    {
        $user = $this->createMock(TwoFactorEmailInterface::class);
        $user->expects($this->never())
            ->method('setEmailAuthCodeExpiresAt')
        ;

        $authCodeProvider = new AuthCodeProvider(
            $this->createStub(PersisterInterface::class),
            $this->authCodeGenerator,
            $this->createStub(AuthCodeMailerInterface::class),
            $this->clock
        );

        $authCodeProvider->createAuthCode($user);
    }

    #[Test]
    public function testThatExpirationIsSetOnUser(): void
    {
        $user = $this->createMock(TwoFactorEmailInterface::class);
        $user->expects($this->once())
            ->method('setEmailAuthCodeExpiresAt')
            ->with($this->clock->now()->add(new \DateInterval('PT5M')))
        ;

        $authCodeProvider = new AuthCodeProvider(
            $this->createStub(PersisterInterface::class),
            $this->authCodeGenerator,
            $this->createStub(AuthCodeMailerInterface::class),
            $this->clock,
            'PT5M'
        );

        $authCodeProvider->createAuthCode($user);
    }
}

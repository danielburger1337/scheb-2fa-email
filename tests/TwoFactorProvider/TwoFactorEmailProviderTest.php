<?php declare(strict_types=1);

namespace danielburger1337\SchebTwoFactorBundle\Tests\TwoFactorProvider;

use danielburger1337\SchebTwoFactorBundle\AuthCodeProvider\AuthCodeProviderInterface;
use danielburger1337\SchebTwoFactorBundle\Tests\MockTwoFactorEmailInterface;
use danielburger1337\SchebTwoFactorBundle\TwoFactorProvider\TwoFactorEmailProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContextInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorFormRendererInterface;
use Symfony\Component\Clock\MockClock;
use Symfony\Component\Security\Core\User\UserInterface;

class TwoFactorEmailProviderTest extends TestCase
{
    private const VALID_AUTH_CODE = '123456';
    private const VALID_AUTH_CODE_WITH_SPACES = '123 456';

    private const INVALID_AUTH_CODE = '654321';

    private MockObject|AuthCodeProviderInterface $authCodeProvider;
    private MockObject|TwoFactorFormRendererInterface $formRenderer;
    private MockClock $clock;
    private TwoFactorEmailProvider $provider;

    protected function setUp(): void
    {
        $this->authCodeProvider = $this->createMock(AuthCodeProviderInterface::class);
        $this->formRenderer = $this->createMock(TwoFactorFormRendererInterface::class);
        $this->clock = new MockClock();

        $this->provider = new TwoFactorEmailProvider($this->authCodeProvider, $this->formRenderer, $this->clock);
    }

    #[Test]
    public function beginAuthenticationTwoFactorPossibleReturnTrue(): void
    {
        $user = $this->createUser(true);
        $context = $this->createAuthenticationContext($user);

        $this->assertTrue($this->provider->beginAuthentication($context));
    }

    #[Test]
    public function beginAuthenticationTwoFactorDisabledReturnFalse(): void
    {
        $user = $this->createUser(false);
        $context = $this->createAuthenticationContext($user);

        $this->assertFalse($this->provider->beginAuthentication($context));
    }

    #[Test]
    public function beginAuthenticationInterfaceNotImplementedReturnFalse(): void
    {
        $user = $this->createMock(UserInterface::class);
        $context = $this->createAuthenticationContext($user);

        $this->assertFalse($this->provider->beginAuthentication($context));
    }

    #[Test]
    public function prepareAuthenticationInterfaceImplementedCreatesAuthCode(): void
    {
        $user = $this->createUser(true);

        $this->authCodeProvider
            ->expects($this->once())
            ->method('createAuthCode')
            ->with($user)
        ;

        $this->provider->prepareAuthentication($user);
    }

    #[Test]
    public function prepareAuthenticationInterfaceNotImplementedDoesNothing(): void
    {
        $user = $this->createMock(UserInterface::class);

        $this->authCodeProvider
            ->expects($this->never())
            ->method('createAuthCode')
        ;

        $this->provider->prepareAuthentication($user);
    }

    #[Test]
    public function validateAuthenticationCodeNoTwoFactorUserReturnFalse(): void
    {
        $user = $this->createMock(UserInterface::class);

        $this->assertFalse($this->provider->validateAuthenticationCode($user, 'foo bar'));
    }

    #[Test]
    public function validateAuthenticationCodeValidCodeReturnTrue(): void
    {
        $user = $this->createUser();
        $user->expects($this->once())
            ->method('getEmailAuthCodeExpiresAt')
            ->willReturn(null)
        ;

        $this->assertTrue($this->provider->validateAuthenticationCode($user, self::VALID_AUTH_CODE));
    }

    #[Test]
    public function validateAuthenticationCodeValidCodeWithSpacesReturnTrue(): void
    {
        $user = $this->createUser();
        $user->expects($this->once())
            ->method('getEmailAuthCodeExpiresAt')
            ->willReturn(null)
        ;

        $this->assertTrue($this->provider->validateAuthenticationCode($user, self::VALID_AUTH_CODE_WITH_SPACES));
    }

    #[Test]
    public function validateAuthenticationCodeInvalidCodeGivenReturnFalse(): void
    {
        $user = $this->createUser();
        $user->expects($this->once())
            ->method('getEmailAuthCodeExpiresAt')
            ->willReturn(null)
        ;

        $this->assertFalse($this->provider->validateAuthenticationCode($user, self::INVALID_AUTH_CODE));
    }

    #[Test]
    public function validateAuthenticationCodeValidExpiredCodeReturnFalseAndCreateNewAuthCode(): void
    {
        $user = $this->createUser();
        $user->expects($this->once())
            ->method('getEmailAuthCodeExpiresAt')
            ->willReturn($this->clock->now())
        ;

        $this->authCodeProvider->expects($this->once())
            ->method('createAuthCode')
            ->with($user)
        ;

        $this->assertFalse($this->provider->validateAuthenticationCode($user, self::VALID_AUTH_CODE));
    }

    #[Test]
    public function validateAuthenticationCodeInvalidExpiredCodeReturnFalseAndCreateNewAuthCode(): void
    {
        $user = $this->createUser();
        $user->expects($this->once())
            ->method('getEmailAuthCodeExpiresAt')
            ->willReturn($this->clock->now())
        ;

        $this->authCodeProvider->expects($this->once())
            ->method('createAuthCode')
            ->with($user)
        ;

        $this->assertFalse($this->provider->validateAuthenticationCode($user, self::INVALID_AUTH_CODE));
    }

    private function createUser(bool $emailAuthEnabled = true): MockObject|MockTwoFactorEmailInterface
    {
        $user = $this->createMock(MockTwoFactorEmailInterface::class);
        $user
            ->expects($this->any())
            ->method('isEmailAuthEnabled')
            ->willReturn($emailAuthEnabled)
        ;
        $user
            ->expects($this->any())
            ->method('getEmailAuthCode')
            ->willReturn(self::VALID_AUTH_CODE)
        ;

        return $user;
    }

    private function createAuthenticationContext(?UserInterface $user = null): MockObject|AuthenticationContextInterface
    {
        $authContext = $this->createMock(AuthenticationContextInterface::class);
        $authContext
            ->expects($this->any())
            ->method('getUser')
            ->willReturn($user ?: $this->createUser())
        ;

        return $authContext;
    }
}

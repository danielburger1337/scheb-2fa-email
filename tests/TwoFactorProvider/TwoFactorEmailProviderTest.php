<?php declare(strict_types=1);

namespace danielburger1337\SchebTwoFactorBundle\Tests\TwoFactorProvider;

use danielburger1337\SchebTwoFactorBundle\AuthCodeProvider\AuthCodeProviderInterface;
use danielburger1337\SchebTwoFactorBundle\Model\TwoFactorEmailInterface;
use danielburger1337\SchebTwoFactorBundle\Tests\MockTwoFactorEmailInterface;
use danielburger1337\SchebTwoFactorBundle\TwoFactorProvider\TwoFactorEmailProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContextInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorFormRendererInterface;
use Symfony\Component\Clock\MockClock;
use Symfony\Component\Security\Core\User\UserInterface;

class TwoFactorEmailProviderTest extends TestCase
{
    private const string VALID_AUTH_CODE = '123456';
    private const string VALID_AUTH_CODE_WITH_SPACES = '123 456';

    private const string INVALID_AUTH_CODE = '654321';

    private Stub|TwoFactorFormRendererInterface $formRenderer;
    private MockClock $clock;

    protected function setUp(): void
    {
        $this->clock = new MockClock();
        $this->formRenderer = $this->createStub(TwoFactorFormRendererInterface::class);
    }

    #[Test]
    public function beginAuthenticationTwoFactorPossibleReturnTrue(): void
    {
        $user = $this->createUser(true);
        $context = $this->createAuthenticationContext($user);

        $this->assertTrue($this->createProvider()->beginAuthentication($context));
    }

    #[Test]
    public function beginAuthenticationTwoFactorDisabledReturnFalse(): void
    {
        $user = $this->createUser(false);
        $context = $this->createAuthenticationContext($user);

        $this->assertFalse($this->createProvider()->beginAuthentication($context));
    }

    #[Test]
    public function beginAuthenticationInterfaceNotImplementedReturnFalse(): void
    {
        $user = $this->createStub(UserInterface::class);
        $context = $this->createAuthenticationContext($user);

        $this->assertFalse($this->createProvider()->beginAuthentication($context));
    }

    #[Test]
    public function prepareAuthenticationInterfaceImplementedCreatesAuthCode(): void
    {
        $user = $this->createStub(TwoFactorEmailInterface::class);

        $authCodeProvider = $this->createMock(AuthCodeProviderInterface::class);
        $authCodeProvider->expects($this->once())
            ->method('createAuthCode')
            ->with($user);

        $this->createProvider($authCodeProvider)->prepareAuthentication($user);
    }

    #[Test]
    public function prepareAuthenticationInterfaceNotImplementedDoesNothing(): void
    {
        $user = $this->createStub(UserInterface::class);

        $authCodeProvider = $this->createMock(AuthCodeProviderInterface::class);
        $authCodeProvider->expects($this->never())
            ->method('createAuthCode');

        $this->createProvider($authCodeProvider)->prepareAuthentication($user);
    }

    #[Test]
    public function validateAuthenticationCodeNoTwoFactorUserReturnFalse(): void
    {
        $user = $this->createStub(UserInterface::class);

        $this->assertFalse($this->createProvider()->validateAuthenticationCode($user, 'foo bar'));
    }

    #[Test]
    public function validateAuthenticationCodeValidCodeReturnTrue(): void
    {
        $user = $this->createUser();
        $user->expects($this->atLeastOnce())
            ->method('getEmailAuthCode')
            ->willReturn(self::VALID_AUTH_CODE);

        $user->expects($this->atLeastOnce())
            ->method('getEmailAuthCodeExpiresAt')
            ->willReturn(null);

        $this->assertTrue($this->createProvider()->validateAuthenticationCode($user, self::VALID_AUTH_CODE));
    }

    #[Test]
    public function validateAuthenticationCodeValidCodeWithSpacesReturnTrue(): void
    {
        $user = $this->createUser();
        $user->expects($this->atLeastOnce())
            ->method('getEmailAuthCode')
            ->willReturn(self::VALID_AUTH_CODE);

        $user->expects($this->atLeastOnce())
            ->method('getEmailAuthCodeExpiresAt')
            ->willReturn(null);

        $this->assertTrue($this->createProvider()->validateAuthenticationCode($user, self::VALID_AUTH_CODE_WITH_SPACES));
    }

    #[Test]
    public function validateAuthenticationCodeInvalidCodeGivenReturnFalse(): void
    {
        $user = $this->createUser();
        $user->expects($this->atLeastOnce())
            ->method('getEmailAuthCodeExpiresAt')
            ->willReturn(null);

        $this->assertFalse($this->createProvider()->validateAuthenticationCode($user, self::INVALID_AUTH_CODE));
    }

    #[Test]
    public function validateAuthenticationCodeValidExpiredCodeReturnFalseAndCreateNewAuthCode(): void
    {
        $user = $this->createUser();
        $user->expects($this->atLeastOnce())
            ->method('getEmailAuthCodeExpiresAt')
            ->willReturn($this->clock->now());

        $authCodeProvider = $this->createMock(AuthCodeProviderInterface::class);
        $authCodeProvider->expects($this->once())
            ->method('createAuthCode')
            ->with($user);

        $this->assertFalse($this->createProvider($authCodeProvider)->validateAuthenticationCode($user, self::VALID_AUTH_CODE));
    }

    #[Test]
    public function validateAuthenticationCodeInvalidExpiredCodeReturnFalseAndCreateNewAuthCode(): void
    {
        $user = $this->createUser();
        $user->expects($this->atLeastOnce())
            ->method('getEmailAuthCodeExpiresAt')
            ->willReturn($this->clock->now())
        ;

        $authCodeProvider = $this->createMock(AuthCodeProviderInterface::class);
        $authCodeProvider->expects($this->once())
            ->method('createAuthCode')
            ->with($user);

        $this->assertFalse($this->createProvider($authCodeProvider)->validateAuthenticationCode($user, self::INVALID_AUTH_CODE));
    }

    private function createUser(?bool $emailAuthEnabled = null): MockObject|MockTwoFactorEmailInterface
    {
        $user = $this->createMock(MockTwoFactorEmailInterface::class);
        if (null !== $emailAuthEnabled) {
            $user->expects($this->atLeastOnce())
                ->method('isEmailAuthEnabled')
                ->willReturn($emailAuthEnabled);
        }

        return $user;
    }

    private function createAuthenticationContext(UserInterface $user): MockObject|AuthenticationContextInterface
    {
        $authContext = $this->createMock(AuthenticationContextInterface::class);
        $authContext->expects($this->atLeastOnce())
            ->method('getUser')
            ->willReturn($user);

        return $authContext;
    }

    private function createProvider(?MockObject $authCodeProvider = null): TwoFactorEmailProvider
    {
        return new TwoFactorEmailProvider(
            $authCodeProvider ?? $this->createStub(AuthCodeProviderInterface::class),
            $this->formRenderer,
            $this->clock
        );
    }
}

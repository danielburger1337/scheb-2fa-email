<?php declare(strict_types=1);

namespace danielburger1337\SchebTwoFactorBundle\Tests\Mailer;

use danielburger1337\SchebTwoFactorBundle\Mailer\SymfonyAuthCodeEmailGenerator;
use danielburger1337\SchebTwoFactorBundle\Model\TwoFactorEmailInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SymfonyAuthCodeEmailGeneratorTest extends TestCase
{
    private const RECIPIENT_EMAIL_ADDRESS = 'recipient@example.com';
    private const SENDER_EMAIL_ADDRESS = 'sender@example.com';
    private const SENDER_NAME = 'Sender Name';
    private const SUBJECT = 'Auth Code';
    private const TEXT_BODY = 'abc: {{AUTH_CODE}}';
    private const AUTH_CODE = '123456';

    private SymfonyAuthCodeEmailGenerator $emailGenerator;

    protected function setUp(): void
    {
        $this->emailGenerator = new SymfonyAuthCodeEmailGenerator(
            self::SUBJECT,
            self::TEXT_BODY,
            self::SENDER_EMAIL_ADDRESS,
            self::SENDER_NAME
        );
    }

    #[Test]
    public function testThatEmailHasCustomValues(): void
    {
        $user = $this->createMock(TwoFactorEmailInterface::class);
        $user
            ->expects($this->once())
            ->method('getEmailAuthRecipient')
            ->willReturn(self::RECIPIENT_EMAIL_ADDRESS)
        ;

        $user
            ->expects($this->once())
            ->method('getEmailAuthCode')
            ->willReturn(self::AUTH_CODE)
        ;

        $message = $this->emailGenerator->createAuthCodeEmail($user);

        $this->assertEquals(self::RECIPIENT_EMAIL_ADDRESS, \current($message->getTo())->getAddress());
        $this->assertEquals(self::SENDER_EMAIL_ADDRESS, \current($message->getFrom())->getAddress());
        $this->assertEquals(self::SENDER_NAME, \current($message->getFrom())->getName());
        $this->assertEquals(self::SUBJECT, $message->getSubject());
        $this->assertEquals('abc: '.self::AUTH_CODE, $message->getBody()->bodyToString());
    }

    #[Test]
    public function testInvalidArgumentExceptionWhenUserHasNoAuthCode(): void
    {
        $user = $this->createMock(TwoFactorEmailInterface::class);
        $user
            ->expects($this->once())
            ->method('getEmailAuthCode')
            ->willReturn(null)
        ;

        $this->expectException(\InvalidArgumentException::class);

        $this->emailGenerator->createAuthCodeEmail($user);
    }
}

<?php declare(strict_types=1);

namespace danielburger1337\SchebTwoFactorBundle\Model;

interface TwoFactorEmailInterface
{
    /**
     * Whether email based two-factor authentication is enabled.
     */
    public function isEmailAuthEnabled(): bool;

    /**
     * The email address to send the auth code to.
     */
    public function getEmailAuthRecipient(): string;

    /**
     * The authentication code.
     */
    public function getEmailAuthCode(): string|null;

    /**
     * Set the authentication code.
     */
    public function setEmailAuthCode(string|null $authCode): void;

    /**
     * Timestamp of when the authentication code will expire.
     */
    public function getEmailAuthCodeExpiresAt(): \DateTimeImmutable|null;

    /**
     * Set the timestamp of when the authentication code will expire.
     */
    public function setEmailAuthCodeExpiresAt(\DateTimeImmutable|null $expiresAt): void;
}

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
    public function getEmailAuthCode(): ?string;

    /**
     * Set the authentication code.
     */
    public function setEmailAuthCode(string $authCode): void;

    /**
     * Timestamp of when the authentication code will expire.
     */
    public function getEmailAuthCodeExpiresAt(): ?\DateTimeImmutable;

    /**
     * Set the timestamp of when the authentication code will expire.
     */
    public function setEmailAuthCodeExpiresAt(\DateTimeImmutable $expiresAt): void;
}

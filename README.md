[![Coding Styles with PHPCSFixer](https://github.com/danielburger1337/scheb-2fa-email/actions/workflows/phpcsfixer.yml/badge.svg)](https://github.com/danielburger1337/scheb-2fa-email/actions/workflows/phpcsfixer.yml)
[![Static Analysis with PHPStan](https://github.com/danielburger1337/scheb-2fa-email/actions/workflows/phpstan.yml/badge.svg)](https://github.com/danielburger1337/scheb-2fa-email/actions/workflows/phpstan.yml)
[![Unit Tests with PHPUnit](https://github.com/danielburger1337/scheb-2fa-email/actions/workflows/phpunit.yml/badge.svg)](https://github.com/danielburger1337/scheb-2fa-email/actions/workflows/phpunit.yml)

# danielburger1337/2fa-email

This bundle is an extension of [scheb/2fa-bundle](https://github.com/scheb/2fa-bundle) that provides a more advanced email two-factor provider than the default [scheb/2fa-email](https://github.com/scheb/2fa-email) provider.

It adds the ability to let an authentication code expire (by default 15 minutes) and makes the customization of the generated
email message a bit more developer friendly.

## Installation

```sh
composer install danielburger1337/2fa-email
```

```php
// config/bundles.php
return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    Scheb\TwoFactorBundle\SchebTwoFactorBundle::class => ['all' => true],
    danielburger1337\SchebTwoFactorBundle\TwoFactorEmailBundle::class => ['all' => true],
];
```

---

## Customize the email

There are multiple ways you can customize the creation and sending of the authentication code message.

By default, the bundle generates a [bare bones](src/Mailer/SymfonyAuthCodeEmailGenerator.php) email.
You can customize the subject and text body by setting the `email_subject`, `email_body`, `sender_email` and `sender_name` parameters.
The "{{AUTH_CODE}}" string in the `email_body` template will be replaced with the actual auth code when the email is sent.

You can change the symfony/mime email message generation (e.g. create a twig TemplatedEmail) by creating a service that implements the
[AuthCodeEmailGeneratorInterface](src/Mailer/AuthCodeEmailGeneratorInterface.php) and setting the `email_generator` parameter
to that service id.

The generated message is then sent via symfony/mailer and the `mailer.mailer` service by default. If you want to use
a different symfony/mailer service to send the messages, simply set the service id to the `symfony_mailer` parameter.

Lastly, if you dont want to use symfony/mailer at all, you can create a service that implements [AuthCodeMailerInterface](src/Mailer/AuthCodeMailerInterface.php) that handles the message generation and sending completly on its own. To use this service,
all you have to do is set the `mailer` parameter to that services id.

---

## Resend an authentication code

If you want to resend the authentication message (maybe the message got lost in transit),
the easiest way is to use a `RequestEvent::class` event listener and inject te
[AuthCodeMailerInterface](src/Mailer/AuthCodeMailerInterface.php) service and call the "sendAuthCode" method.

Using a "normal" route doesn't work by default because scheb/2fa-bundle will always redirect that route to the
2fa endpoint.

```php
declare(strict_types=1);

use danielburger1337\SchebTwoFactorBundle\Mailer\AuthCodeMailerInterface;
use danielburger1337\SchebTwoFactorBundle\Model\TwoFactorEmailInterface;
use Scheb\TwoFactorBundle\Security\Authentication\Token\TwoFactorTokenInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsEventListener(RequestEvent::class)]
class ResendEmailAuthCodeEventListener
{

    public function __construct(
        private readonly RateLimiterFactory $rateLimiterFactory,
        private readonly AuthCodeMailerInterface $authCodeMailer,
        private readonly TokenStorageInterface $tokenStorage,
    ) {
    }

    public function __invoke(RequestEvent $request): void
    {
        $request = $event->getRequest();

        if ($request->attributes->get('_route') !== '2fa_login') {
            return;
        }

        $token = $this->tokenStorage->getToken();
        $user = $token?->getUser();

        if (!$token instanceof TwoFactorTokenInterface || !$user instanceof TwoFactorEmailInterface) {
            return;
        }

        // somehow determine that you want to resend the email
        if ($request->request->get('resendAuthCode') === 'true') {
            // If you use rate limiting, make sure to also use the auth code as key,
            // otherwise the user might get throttled when their code has expired and a new one should be sent.
            $rateLimiter = $this->rateLimiterFactory->create(
                'tfa_email_'.\hash('xxh128', $user->getEmailAuthCode().$user->getEmailAuthRecipient())
            );

            if ($rateLimiter->consume(1)->isAccepted()) {
                // mail the auth code
                $this->authCodeMailer->sendAuthCode($user);
            }
        }
    }

}
```

---

## Configuration Reference

The listed values are the default values. Every value is optional.

```yaml
# config/packages/two_factor_email.yaml
two_factor_email:
    # A custom service to manage the auth code
    # It must implement AuthCodeProviderInterface
    auth_code_provider: null
    # This option is only used when the default `auth_code_provider` is used.
    # A \DateInterval compatioble value that sets
    # how long an auth code is considered valid.
    # `null` disables expiration.
    expires_after: PT15M

    # ---------------------------------------------

    # A custom service that creates the auth code
    # It must implement AuthCodeGeneratorInterface
    code_generator: null
    # This option is only used when the default `code_generator` is used.
    # The length of the generated auth code
    digits: 6

    # A custom service that sends the auth code to the user
    # It must implement AuthCodeMailerInterface
    # The default implementation has a hard dependency on symonfy/mailer,
    # so make sure that you have the package installed.
    mailer: null
    # A custom symfony/mailer service to send the emails with.
    # "mailer.mailer" is the default symfony/mailer service.
    symfony_mailer: mailer.mailer
    # A custom service that generates the mime email messsage to send
    # It must implement AuthCodeEmailGeneratorInterface.
    email_generator: null
    # Subject of the generated email
    email_subject: Authentication Code
    # Text message body of the generated email
    # "{{AUTH_CODE}}" is a template string that will be replaced with the actual auth code.
    email_body: "{{AUTH_CODE}}"
    # "From" header address
    sender_email: null
    # "From" header name
    sender_name: null

    # A custom form renderer service that renders the 2fa form.
    # It must implement TwoFactorFormRendererInterface.
    form_renderer: null
    # The twig template to render when no custom form renderer was defined.
    template: "@SchebTwoFactor/Authentication/form.html.twig"
```

---

## License

This software is available under the [MIT](LICENSE) license.

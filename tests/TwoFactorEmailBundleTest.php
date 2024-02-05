<?php declare(strict_types=1);

namespace danielburger1337\SchebTwoFactorBundle\Tests;

use danielburger1337\SchebTwoFactorBundle\AuthCodeProvider\AuthCodeGenerator;
use danielburger1337\SchebTwoFactorBundle\AuthCodeProvider\AuthCodeGeneratorInterface;
use danielburger1337\SchebTwoFactorBundle\AuthCodeProvider\AuthCodeProvider;
use danielburger1337\SchebTwoFactorBundle\AuthCodeProvider\AuthCodeProviderInterface;
use danielburger1337\SchebTwoFactorBundle\Mailer\AuthCodeEmailGeneratorInterface;
use danielburger1337\SchebTwoFactorBundle\Mailer\AuthCodeMailerInterface;
use danielburger1337\SchebTwoFactorBundle\Mailer\SymfonyAuthCodeEmailGenerator;
use danielburger1337\SchebTwoFactorBundle\Mailer\SymfonyAuthCodeMailer;
use danielburger1337\SchebTwoFactorBundle\TwoFactorEmailBundle;
use danielburger1337\SchebTwoFactorBundle\TwoFactorProvider\TwoFactorEmailProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\DefaultTwoFactorFormRenderer;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;

class TwoFactorEmailBundleTest extends TestCase
{
    private ContainerBuilder $container;
    private ContainerConfigurator $configurator;
    private TwoFactorEmailBundle $bundle;

    protected function setUp(): void
    {
        $this->container = new ContainerBuilder();

        $loader = new PhpFileLoader($this->container, new FileLocator(\dirname(__DIR__).'/src'));
        $instanceOf = [];

        $this->configurator = new ContainerConfigurator($this->container, $loader, $instanceOf, '', '');
        $this->bundle = new TwoFactorEmailBundle();
    }

    #[Test]
    public function testDefaultConfig(): void
    {
        $this->loadExtension();

        $this->assertTrue($this->container->has(AuthCodeGenerator::class));
        $this->assertTrue($this->container->hasAlias(AuthCodeGeneratorInterface::class));

        $this->assertTrue($this->container->hasAlias(AuthCodeProviderInterface::class));
        $this->assertTrue($this->container->has(AuthCodeProvider::class));

        $this->assertTrue($this->container->hasAlias(AuthCodeMailerInterface::class));
        $this->assertTrue($this->container->has(SymfonyAuthCodeMailer::class));
        $this->assertTrue($this->container->hasAlias(AuthCodeEmailGeneratorInterface::class));
        $this->assertTrue($this->container->has(SymfonyAuthCodeEmailGenerator::class));

        $this->assertTrue($this->container->has(TwoFactorEmailProvider::class));
        $this->assertTrue($this->container->has('danielburger1337.two_factor_email.form_renderer'));
    }

    #[Test]
    public function testTwoFactorProviderTag(): void
    {
        $this->loadExtension();

        $definition = $this->container->getDefinition(TwoFactorEmailProvider::class);

        $this->assertTrue($definition->hasTag('scheb_two_factor.provider'));

        $tag = $definition->getTag('scheb_two_factor.provider');

        $this->assertArrayHasKey(0, $tag);
        $this->assertIsArray($tag[0]);
        $this->assertArrayHasKey('alias', $tag[0]);
        $this->assertEquals('db1337email', $tag[0]['alias']);
    }

    #[Test]
    public function testThatCustomAuthCodeGeneratorIsRegistered(): void
    {
        $this->loadExtension(['code_generator' => 'acme.code_generator']);

        $this->assertFalse($this->container->has(AuthCodeGenerator::class));
        $this->assertTrue($this->container->hasAlias(AuthCodeGeneratorInterface::class));

        $this->assertEquals('acme.code_generator', $this->container->getAlias(AuthCodeGeneratorInterface::class)->__toString());
    }

    #[Test]
    public function testThatCustomAuthCodeGeneratorLengthIsSet(): void
    {
        $this->loadExtension(['digits' => 69]);

        $this->assertTrue($this->container->has(AuthCodeGenerator::class));

        $this->assertEquals(AuthCodeGenerator::class, $this->container->getAlias(AuthCodeGeneratorInterface::class)->__toString());

        $this->assertEquals(69, $this->container->getDefinition(AuthCodeGenerator::class)->getArgument('$digits'));
    }

    #[Test]
    public function testThatCustomAuthCodeMailerIsRegistered(): void
    {
        $this->loadExtension(['mailer' => 'acme.mailer']);

        $this->assertFalse($this->container->has(SymfonyAuthCodeMailer::class));
        $this->assertFalse($this->container->has(SymfonyAuthCodeEmailGenerator::class));
        $this->assertFalse($this->container->hasAlias(AuthCodeEmailGeneratorInterface::class));

        $this->assertEquals('acme.mailer', $this->container->getAlias(AuthCodeMailerInterface::class)->__toString());
    }

    #[Test]
    public function testThatCustomSymonfyMailerIsUsed(): void
    {
        $this->loadExtension(['symfony_mailer' => 'acme.mailer']);

        $this->assertTrue($this->container->has(SymfonyAuthCodeMailer::class));

        /** @var Reference */
        $ref = $this->container->getDefinition(SymfonyAuthCodeMailer::class)->getArgument('$mailer');

        $this->assertEquals('acme.mailer', $ref->__toString());
    }

    #[Test]
    public function testThatCustomEmailGeneratorIsRegistered(): void
    {
        $this->loadExtension(['email_generator' => 'acme.email_generator']);

        $this->assertTrue($this->container->has(SymfonyAuthCodeMailer::class));
        $this->assertEquals(SymfonyAuthCodeMailer::class, $this->container->getAlias(AuthCodeMailerInterface::class)->__toString());

        $this->assertTrue($this->container->hasAlias(AuthCodeEmailGeneratorInterface::class));
        $this->assertFalse($this->container->has(SymfonyAuthCodeEmailGenerator::class));

        $this->assertEquals('acme.email_generator', $this->container->getAlias(AuthCodeEmailGeneratorInterface::class)->__toString());
    }

    #[Test]
    public function testThatEmailGeneratorOptionsAreSet(): void
    {
        $this->loadExtension([
            'email_subject' => 'Your Auth Code',
            'email_body' => 'abc: {{AUTH_CODE}}',
            'sender_email' => 'sender@example.com',
            'sender_name' => 'sender',
        ]);

        $this->assertTrue($this->container->has(SymfonyAuthCodeEmailGenerator::class));
        $this->assertTrue($this->container->hasAlias(AuthCodeEmailGeneratorInterface::class));

        $this->assertEquals(SymfonyAuthCodeMailer::class, $this->container->getAlias(AuthCodeMailerInterface::class)->__toString());

        $def = $this->container->getDefinition(SymfonyAuthCodeEmailGenerator::class);

        $this->assertEquals('sender@example.com', $def->getArgument('$senderEmail'));
        $this->assertEquals('sender', $def->getArgument('$senderName'));
        $this->assertEquals('Your Auth Code', $def->getArgument('$subject'));
        $this->assertEquals('abc: {{AUTH_CODE}}', $def->getArgument('$textBody'));
    }

    #[Test]
    public function testThatCustomAuthCodeProviderIsRegistered(): void
    {
        $this->loadExtension(['auth_code_provider' => 'acme.provider']);

        $this->assertFalse($this->container->has(AuthCodeProvider::class));
        $this->assertTrue($this->container->hasAlias(AuthCodeProviderInterface::class));

        $this->assertEquals('acme.provider', $this->container->getAlias(AuthCodeProviderInterface::class)->__toString());
    }

    #[Test]
    public function testThatAuthCodeProviderExpirationIsSet(): void
    {
        $this->loadExtension(['expires_after' => 'PT5M']);

        $this->assertTrue($this->container->has(AuthCodeProvider::class));
        $this->assertTrue($this->container->hasAlias(AuthCodeProviderInterface::class));
        $this->assertEquals(AuthCodeProvider::class, $this->container->getAlias(AuthCodeProviderInterface::class)->__toString());

        $this->assertEquals('PT5M', $this->container->getDefinition(AuthCodeProvider::class)->getArgument('$expiresAfter'));
    }

    #[Test]
    public function testThatFormRendererTemplateIsSet(): void
    {
        $this->loadExtension(['template' => 'test.twig']);

        $this->assertTrue($this->container->has('danielburger1337.two_factor_email.form_renderer'));

        $def = $this->container->getDefinition('danielburger1337.two_factor_email.form_renderer');

        $this->assertEquals(DefaultTwoFactorFormRenderer::class, $def->getClass());
        $this->assertEquals('test.twig', $def->getArgument(1));
    }

    #[Test]
    public function testThatCustomFormRendererIsRegistered(): void
    {
        $this->loadExtension(['form_renderer' => 'acme.form_renderer']);

        $this->assertFalse($this->container->has('danielburger1337.two_factor_email.form_renderer'));

        /** @var Reference */
        $ref = $this->container->getDefinition(TwoFactorEmailProvider::class)->getArgument('$formRenderer');

        $this->assertEquals('acme.form_renderer', $ref->__toString());
    }

    private function loadExtension(array $config = []): void
    {
        $this->bundle->loadExtension([
            'auth_code_provider' => null,
            'expires_after' => 'PT15M',

            'code_generator' => null,
            'digits' => 6,

            'mailer' => null,
            'symfony_mailer' => 'mailer.mailer',
            'email_generator' => null,
            'email_subject' => 'Authentication Code',
            'email_body' => '{{AUTH_CODE}}',
            'sender_email' => null,
            'sender_name' => null,

            'form_renderer' => null,
            'template' => '@SchebTwoFactor/Authentication/form.html.twig',
            ...$config,
        ], $this->configurator, $this->container);
    }
}

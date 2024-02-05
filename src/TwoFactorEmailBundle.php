<?php declare(strict_types=1);

namespace danielburger1337\SchebTwoFactorBundle;

use danielburger1337\SchebTwoFactorBundle\AuthCodeProvider\AuthCodeGenerator;
use danielburger1337\SchebTwoFactorBundle\AuthCodeProvider\AuthCodeGeneratorInterface;
use danielburger1337\SchebTwoFactorBundle\AuthCodeProvider\AuthCodeProvider;
use danielburger1337\SchebTwoFactorBundle\AuthCodeProvider\AuthCodeProviderInterface;
use danielburger1337\SchebTwoFactorBundle\Mailer\AuthCodeEmailGeneratorInterface;
use danielburger1337\SchebTwoFactorBundle\Mailer\AuthCodeMailerInterface;
use danielburger1337\SchebTwoFactorBundle\Mailer\SymfonyAuthCodeEmailGenerator;
use danielburger1337\SchebTwoFactorBundle\Mailer\SymfonyAuthCodeMailer;
use danielburger1337\SchebTwoFactorBundle\TwoFactorProvider\TwoFactorEmailProvider;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\DefaultTwoFactorFormRenderer;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

class TwoFactorEmailBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('../config/definitions/*.php');
    }

    /**
     * @param array<string, mixed> $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $this->configureAuthCodeGenerator($config, $container);
        $this->configureAuthCodeMailer($config, $container);
        $this->configureAuthCodeProvider($config, $container);
        $this->configureTwoFactorProvider($config, $container);
    }

    /**
     * @param array<string, mixed> $config
     */
    private function configureAuthCodeGenerator(array $config, ContainerConfigurator $container): void
    {
        /** @var string|null */
        $authCodeGenerator = $config['code_generator'];

        if (null === $authCodeGenerator) {
            $container->import('../config/services/code_generator.php');

            $container->services()->get(AuthCodeGenerator::class)
                ->arg('$digits', $config['digits'])
            ;

            $authCodeGenerator = AuthCodeGenerator::class;
        }

        $container->services()->alias(AuthCodeGeneratorInterface::class, (string) $authCodeGenerator);
    }

    /**
     * @param array<string, mixed> $config
     */
    private function configureAuthCodeMailer(array $config, ContainerConfigurator $container): void
    {
        /** @var string|null */
        $mailer = $config['mailer'];

        if (null === $mailer) {
            $container->import('../config/services/mailer.php');

            /** @var string|null */
            $emailGenerator = $config['email_generator'];

            if (null !== $emailGenerator) {
                $container->services()->remove(SymfonyAuthCodeEmailGenerator::class);
            } else {
                $container->services()->get(SymfonyAuthCodeEmailGenerator::class)
                    ->arg('$subject', $config['email_subject'])
                    ->arg('$textBody', $config['email_body'])
                    ->arg('$senderEmail', $config['sender_email'])
                    ->arg('$senderName', $config['sender_name'])
                ;

                $emailGenerator = SymfonyAuthCodeEmailGenerator::class;
            }

            $container->services()->alias(AuthCodeEmailGeneratorInterface::class, $emailGenerator);

            /** @var string */
            $symfonyMailer = $config['symfony_mailer'];

            $container->services()->get(SymfonyAuthCodeMailer::class)
                ->arg('$mailer', service($symfonyMailer))
                ->arg('$authCodeEmailGenerator', service($emailGenerator))
            ;

            $mailer = SymfonyAuthCodeMailer::class;
        }

        $container->services()->alias(AuthCodeMailerInterface::class, (string) $mailer);
    }

    /**
     * @param array<string, mixed> $config
     */
    private function configureAuthCodeProvider(array $config, ContainerConfigurator $container): void
    {
        /** @var string|null */
        $provider = $config['auth_code_provider'];

        if (null === $provider) {
            $container->import('../config/services/auth_code_provider.php');

            $container->services()->get(AuthCodeProvider::class)
                ->arg('$expiresAfter', $config['expires_after'])
            ;

            $provider = AuthCodeProvider::class;
        }

        $container->services()->alias(AuthCodeProviderInterface::class, (string) $provider);
    }

    /**
     * @param array<string, mixed> $config
     */
    private function configureTwoFactorProvider(array $config, ContainerConfigurator $container): void
    {
        $container->import('../config/services/two_factor_provider.php');

        /** @var string|null */
        $formRenderer = $config['form_renderer'];

        if (null === $formRenderer) {
            $formRenderer = 'danielburger1337.two_factor_email.form_renderer';

            $container->services()->set($formRenderer, DefaultTwoFactorFormRenderer::class)
                ->arg(0, service('twig'))
                ->arg(1, $config['template'])
                ->lazy(true)
            ;
        }

        $container->services()->get(TwoFactorEmailProvider::class)
            ->arg('$formRenderer', service($formRenderer))
        ;
    }
}

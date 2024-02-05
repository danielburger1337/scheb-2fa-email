<?php declare(strict_types=1);

namespace Symfony\Component\Config\Definition\Configurator;

use danielburger1337\SchebTwoFactorBundle\Mailer\AuthCodeEmailGeneratorInterface;
use danielburger1337\SchebTwoFactorBundle\Mailer\AuthCodeMailerInterface;
use Symfony\Component\Mailer\MailerInterface;

return static function (DefinitionConfigurator $definition): void {
    $definition
        ->rootNode()
            ->children()
                ->scalarNode('mailer')
                    ->defaultNull()
                    ->info(\sprintf('Custom auth code mailer service that must implement "%s"', AuthCodeMailerInterface::class))
                ->end()

                ->scalarNode('symfony_mailer')
                    ->cannotBeEmpty()
                    ->defaultValue('mailer.mailer')
                    ->info(\sprintf('Custom symfony mailer service that must implement "%s"', MailerInterface::class))
                ->end()

                ->scalarNode('email_generator')
                    ->defaultNull()
                    ->info(\sprintf('Custom email generator service that must implement "%s"', AuthCodeEmailGeneratorInterface::class))
                ->end()

                ->scalarNode('email_subject')
                    ->cannotBeEmpty()
                    ->defaultValue('Authentication Code')
                    ->info('The subject of the email message.')
                ->end()
                ->scalarNode('email_body')
                    ->cannotBeEmpty()
                    ->defaultValue('{{AUTH_CODE}}')
                    ->info('The text message body. The string "{{AUTH_CODE}}" will be replaced by the users auth code.')
                ->end()

                ->scalarNode('sender_email')
                    ->info('The email address of the sender.')
                    ->defaultNull()
                ->end()
                ->scalarNode('sender_name')
                    ->info('The name of the sender.')
                    ->defaultNull()
                ->end()
            ->end()
        ->end()
    ;
};

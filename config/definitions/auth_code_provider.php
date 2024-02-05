<?php declare(strict_types=1);

namespace Symfony\Component\Config\Definition\Configurator;

use danielburger1337\SchebTwoFactorBundle\AuthCodeProvider\AuthCodeProviderInterface;
use PhpCsFixer\ConfigurationException\InvalidConfigurationException;

return static function (DefinitionConfigurator $definition): void {
    $definition
        ->rootNode()
            ->children()
                ->scalarNode('auth_code_provider')
                    ->defaultNull()
                    ->info(\sprintf('Custom auth code provider service that must implement "%s"', AuthCodeProviderInterface::class))
                ->end()

                ->scalarNode('expires_after')
                    ->defaultValue('PT15M')
                    ->validate()
                        ->ifString()
                        ->then(static function (string $value): string {
                            try {
                                new \DateInterval($value);
                            } catch (\Throwable) {
                                throw new InvalidConfigurationException('"two_factor_email.expires_after" is not a valid \DateInterval value.');
                            }

                            return $value;
                        })
                    ->end()
                ->end()
            ->end()
        ->end()
    ;
};

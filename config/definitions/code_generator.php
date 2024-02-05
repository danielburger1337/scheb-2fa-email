<?php declare(strict_types=1);

namespace Symfony\Component\Config\Definition\Configurator;

use danielburger1337\SchebTwoFactorBundle\AuthCodeProvider\AuthCodeGeneratorInterface;

return static function (DefinitionConfigurator $definition): void {
    $definition
        ->rootNode()
            ->children()
                ->scalarNode('code_generator')
                    ->defaultNull()
                    ->info(\sprintf('Custom auth code generator service that must implement "%s"', AuthCodeGeneratorInterface::class))
                ->end()

                ->integerNode('digits')
                    ->info('The number of digits the generated auth code will have.')
                    ->min(1)
                    ->defaultValue(6)
                ->end()
            ->end()
        ->end()
    ;
};

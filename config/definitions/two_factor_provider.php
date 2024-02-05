<?php declare(strict_types=1);

namespace Symfony\Component\Config\Definition\Configurator;

use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\DefaultTwoFactorFormRenderer;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorFormRendererInterface;

return static function (DefinitionConfigurator $definition): void {
    $definition
        ->rootNode()
            ->children()
                ->scalarNode('form_renderer')
                    ->defaultNull()
                    ->info(\sprintf('A custom form renderer service that must implement "%s"', TwoFactorFormRendererInterface::class))
                ->end()

                ->scalarNode('template')
                    ->cannotBeEmpty()
                    ->defaultValue('@SchebTwoFactor/Authentication/form.html.twig')
                    ->info(\sprintf('Twig template to pass to "%s"', DefaultTwoFactorFormRenderer::class))
                ->end()
            ->end()
        ->end()
    ;
};

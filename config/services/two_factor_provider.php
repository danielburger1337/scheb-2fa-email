<?php declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use danielburger1337\SchebTwoFactorBundle\AuthCodeProvider\AuthCodeProviderInterface;
use danielburger1337\SchebTwoFactorBundle\TwoFactorProvider\TwoFactorEmailProvider;
use Symfony\Component\Clock\ClockInterface;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->set(TwoFactorEmailProvider::class)
            ->args([
                '$authCodeProvider' => service(AuthCodeProviderInterface::class),
                '$formRenderer' => abstract_arg('form renderer'),
                '$clock' => service(ClockInterface::class),
            ])
            ->tag('scheb_two_factor.provider', ['alias' => 'email'])
    ;
};

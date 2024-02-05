<?php declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use danielburger1337\SchebTwoFactorBundle\AuthCodeProvider\AuthCodeGenerator;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->set(AuthCodeGenerator::class)
            ->args([
                '$digits' => abstract_arg(''),
            ])
    ;
};

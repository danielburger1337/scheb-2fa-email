<?php declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use danielburger1337\SchebTwoFactorBundle\AuthCodeProvider\AuthCodeGeneratorInterface;
use danielburger1337\SchebTwoFactorBundle\AuthCodeProvider\AuthCodeProvider;
use danielburger1337\SchebTwoFactorBundle\Mailer\AuthCodeMailerInterface;
use Symfony\Component\Clock\ClockInterface;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->set(AuthCodeProvider::class)
            ->args([
                '$persister' => service('scheb_two_factor.persister'),
                '$authCodeGenerator' => service(AuthCodeGeneratorInterface::class),
                '$mailer' => service(AuthCodeMailerInterface::class),
                '$clock' => service(ClockInterface::class),
                '$expiresAfter' => abstract_arg('DateInterval after which the auth code is considered expired'),
            ])
    ;
};

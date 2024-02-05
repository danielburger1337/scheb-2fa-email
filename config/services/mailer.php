<?php declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use danielburger1337\SchebTwoFactorBundle\Mailer\SymfonyAuthCodeEmailGenerator;
use danielburger1337\SchebTwoFactorBundle\Mailer\SymfonyAuthCodeMailer;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->set(SymfonyAuthCodeMailer::class)
            ->args([
                '$mailer' => abstract_arg('symfony mailer'),
                '$authCodeEmailGenerator' => abstract_arg('email generator'),
            ])

        ->set(SymfonyAuthCodeEmailGenerator::class)
            ->args([
                '$subject' => abstract_arg('Subject of the email'),
                '$textBody' => abstract_arg('text/plain body of the email'),
                '$senderEmail' => abstract_arg('Email address of the sender'),
                '$senderName' => abstract_arg('Name of the email sender'),
            ])
    ;
};

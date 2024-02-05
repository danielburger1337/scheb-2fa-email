<?php declare(strict_types=1);

namespace danielburger1337\SchebTwoFactorBundle\Tests;

use danielburger1337\SchebTwoFactorBundle\Model\TwoFactorEmailInterface;
use Symfony\Component\Security\Core\User\UserInterface;

interface MockTwoFactorEmailInterface extends UserInterface, TwoFactorEmailInterface
{
}

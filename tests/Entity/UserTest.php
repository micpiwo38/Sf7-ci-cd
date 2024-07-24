<?php

namespace App\Tests;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserTest extends KernelTestCase
{


    public function testSomething(User $user, int $nbr_errors): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $errors = $container->get('validator')->validate($user);
        $this->assertCount($nbr_errors, $errors);

    }
}

<?php

namespace App\DataFixtures;

use App\Entity\Role;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $roles = ['Admin', 'organisateur', 'participant'];

        foreach ($roles as $index => $name) {
            $role = new Role();
            $role->setRoleName($name);
            $manager->persist($role);
        }

        $manager->flush();
    }
}
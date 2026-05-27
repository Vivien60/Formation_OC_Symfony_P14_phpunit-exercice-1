<?php

namespace App\Doctrine\DataFixtures;

use App\Model\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use function array_fill_callback;

final class UserFixtures extends Fixture
{
    public const int NB_TO_CREATE = 10;

    public function load(ObjectManager $manager): void
    {
        $users = array_fill_callback(0, self::NB_TO_CREATE, fn (int $index): User => (new User)
            ->setEmail(sprintf('user+%d@email.com', $index))
            ->setPlainPassword('password')
            ->setUsername(sprintf('user+%d', $index))
        );

        foreach ($users as $key => $user) {
            $this->addReference(self::getObjectReference($key), $user);
            $manager->persist($user);
        }

        $manager->flush();
    }

    public static function getObjectReference(int $index): string
    {
        return User::class . "_" . $index;
    }
}
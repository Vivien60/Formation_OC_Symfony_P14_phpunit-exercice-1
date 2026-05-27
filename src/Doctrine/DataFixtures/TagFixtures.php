<?php

namespace App\Doctrine\DataFixtures;

use App\Model\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;

final class TagFixtures extends Fixture
{
    public function __construct(
        private readonly Generator $faker,
    ) {
    }

    public function load(ObjectManager $manager): void
    {

        //création de tags
        $nbTagsToCreate = VideoGameFixtures::NB_TO_CREATE * 2;
        $tags = $this->loadTags($nbTagsToCreate);

        //persistence
        array_walk($tags, [$manager, 'persist']);

        $manager->flush();
    }

    /**
     * @param float|int $nbTagsToCreate
     * @return array
     */
    protected function loadTags(float|int $nbTagsToCreate): array
    {
        $tags = [];
        for ($i = 0; $i < $nbTagsToCreate; $i++) {
            $tags[] = (new Tag)->setName($this->faker->word());
        }
        return $tags;
    }
}
<?php

namespace App\Doctrine\DataFixtures;

use App\Model\Entity\Review;
use App\Model\Entity\Tag;
use App\Model\Entity\User;
use App\Model\Entity\VideoGame;
use App\Rating\CalculateAverageRating;
use App\Rating\CountRatingsPerValue;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;

final class VideoGameFixtures extends Fixture implements DependentFixtureInterface
{
    public const int NB_TO_CREATE = 50;

    public function __construct(
        private readonly Generator $faker,
        private readonly CalculateAverageRating $calculateAverageRating,
        private readonly CountRatingsPerValue $countRatingsPerValue,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $tags = $manager->getRepository(Tag::class)->findAll();
        $users = $manager->getRepository(User::class)->findAll();

        $videoGames = $this->loadVideoGames();
        $this->addTagsToVideoGames($videoGames, $tags);

        array_walk($videoGames, [$manager, 'persist']);

        // Ajout des reviews aux jeux vidéos
        $nbReviewsToCreate = self::NB_TO_CREATE * 2;
        $reviews = $this->loadReviewsAndAddToVideoGames($videoGames, $users, $nbReviewsToCreate);
        array_walk($reviews, [$manager, 'persist']);

        $manager->flush();
    }

    /**
     * @return VideoGame[]
     */
    protected function loadVideoGames(): array
    {
        $videoGames = \array_fill_callback(0, self::NB_TO_CREATE, fn (int $index): VideoGame => (new VideoGame())
            ->setTitle(sprintf('Jeu vidéo %d', $index))
            ->setDescription($this->faker->paragraphs(10, true))
            ->setReleaseDate(new \DateTimeImmutable())
            ->setTest($this->faker->paragraphs(6, true))
            ->setRating(($index % 5) + 1)
            ->setImageName(sprintf('video_game_%d.png', $index))
            ->setImageSize(2_098_872)
        );

        return $videoGames;
    }

    protected function addTagsToVideoGames(array $videoGames, array $tags, int $nbTagsByVideoGame = 2): void
    {
        //        $maxIndex = count($tags) - 1;
        //        foreach ($videoGames as $videoGame) {
        //            for ($j = 0; $j < $nbTagsByVideoGame; $j++) {
        //                $videoGame->getTags()->add($tags[random_int(0, $maxIndex)]);
        //            }
        //        }
        // Attache un tag à 10 jeux et un jeu à 5 tags
        array_walk($videoGames, static function (VideoGame $videoGame, int $index) use ($tags) {
            for ($tagIndex = 0; $tagIndex < 5; ++$tagIndex) {
                $videoGame->getTags()->add($tags[($index + $tagIndex) % count($tags)]);
            }
        });
    }

    /**
     * @throws \Random\RandomException
     */
    protected function loadReviewsAndAddToVideoGames(array $videoGames, array $users, float|int $nbReviewsToCreate): array
    {
        $reviews = [];
        for ($i = 0; $i < $nbReviewsToCreate; ++$i) {
            $videoGame = $this->getRandomVideoGame($videoGames);
            $user = $this->getRandomUser($users);
            $review = $this->loadReview($videoGame, $user);
            $videoGame->getReviews()->add($review);
            $reviews[] = $review;
        }
        $this->calculateAverageRating($videoGames);
        $this->countRatingsPerValue($videoGames);

        return $reviews;
    }

    /**
     * @throws \Random\RandomException
     */
    protected function loadReview(VideoGame $videoGame, User $user): Review
    {
        $review = (new Review())
            ->setComment($this->faker->paragraph())
            ->setRating(random_int(1, 5))
            ->setVideoGame($videoGame)
            ->setUser($user);

        return $review;
    }

    /**
     * @throws \Random\RandomException
     */
    protected function getRandomVideoGame(array $videoGames): VideoGame
    {
        $randomIndex = random_int(0, count($videoGames) - 1);

        return $videoGames[$randomIndex];
    }

    /**
     * @throws \Random\RandomException
     */
    protected function getRandomUser(array $users): User
    {
        $randomIndex = random_int(1, count($users) - 1);

        return $users[$randomIndex];
    }

    protected function calculateAverageRating(array $videoGames): void
    {
        foreach ($videoGames as $videoGame) {
            $this->calculateAverageRating->calculateAverage($videoGame);
        }
    }

    protected function countRatingsPerValue(array $videoGames): void
    {
        foreach ($videoGames as $videoGame) {
            $this->countRatingsPerValue->countRatingsPerValue($videoGame);
        }
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            TagFixtures::class,
        ];
    }
}

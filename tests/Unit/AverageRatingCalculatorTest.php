<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Model\Entity\Review;
use App\Model\Entity\VideoGame;
use App\Rating\CalculateAverageRating;
use App\Rating\RatingHandler;

class AverageRatingCalculatorTest extends \PHPUnit\Framework\TestCase
{

    private function createCalculator(): CalculateAverageRating
    {
        return new RatingHandler();
    }

    /**
     * @dataProvider reviewsProvider
     */
    public function testCalculatingAverage(array $reviews, int $expectedAverageNote): void
    {
        $videoGame = new VideoGame();

        foreach ($reviews as $review) {
            $videoGame->getReviews()->add($review);
        }

        $calculator = $this->createCalculator();
        $calculator->calculateAverage($videoGame);

        $this->assertEquals($expectedAverageNote, $videoGame->getAverageRating());
    }

    public function testCalculatingAverageWithNoReview(): void
    {
        $videoGame = new VideoGame();
        $calculator = $this->createCalculator();
        $calculator->calculateAverage($videoGame);
        $this->assertEquals(0, $videoGame->getAverageRating());
    }

    public function testAddingNoteBelowOneShouldFail(): void
    {
        $this->expectException(\Exception::class);
        $videoGame = new VideoGame();
        $videoGame->getReviews()->add(new Review()->setRating(0));
        $calculator = $this->createCalculator();
        $calculator->calculateAverage($videoGame);
    }


    public static function reviewsProvider() : array
    {
        $manyReviews = [
            new Review()->setRating(1),
            new Review()->setRating(1),
            new Review()->setRating(5),
        ];
        $manyReviewsExpectation = [$manyReviews, 3];

        $onlyOneReview = [ new Review()->setRating(2) ];
        $onlyOneReviewExpectation = [$onlyOneReview, 2];

        return [
            "many reviews" =>  $manyReviewsExpectation,
            "only one review" => $onlyOneReviewExpectation
        ];
    }
}
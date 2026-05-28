<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Model\Entity\NumberOfRatingPerValue;
use App\Model\Entity\Review;
use App\Model\Entity\VideoGame;
use App\Rating\CountRatingsPerValue;
use App\Rating\RatingHandler;

class NoteCalculatorTest extends \PHPUnit\Framework\TestCase
{

    private function createCalculator() : CountRatingsPerValue
    {
        return new RatingHandler();
    }

    /**
     * @dataProvider reviewsProvider
     */
    public function testCountingRatingsPerValue($reviews, $expectedRepartition)
    {
        $videoGame = new VideoGame();

        foreach ($reviews as $review) {
            $videoGame->getReviews()->add($review);
        }

        $calculator = $this->createCalculator();
        $calculator->countRatingsPerValue($videoGame);
        $this->assertCountRatesPerValueEquals($expectedRepartition, $videoGame->getNumberOfRatingsPerValue());
    }

    public static function reviewsProvider() : array
    {
        $expectedRepartition = [
            'numberOfOne' => 0,
            'numberOfTwo' => 0,
            'numberOfThree' => 0,
            'numberOfFour' => 0,
            'numberOfFive' => 0,
        ];

        //plusieurs revues, qui créé un ensemble où
        // chaque note est répétée un nombre variable de fois
        $manyReviews = [
            new Review()->setRating(1),
            new Review()->setRating(1),
            new Review()->setRating(2),
            new Review()->setRating(2),
            new Review()->setRating(3),
            new Review()->setRating(3),
            new Review()->setRating(3),
            new Review()->setRating(4),
            new Review()->setRating(4),
            new Review()->setRating(5),
        ];
        $manyReviewsExpectation = [
            $manyReviews,
            array_merge($expectedRepartition, [
                'numberOfOne' => 2,
                'numberOfTwo' => 2,
                'numberOfThree' => 3,
                'numberOfFour' => 2,
                'numberOfFive' => 1,
            ])
        ];

        //un ensemble par note comprenant une seule revue avec cette note
        $onlyOneReviewExpectation = [];
        foreach(range(1, 5) as $note) {
            $expectedRepartitionTmp = $expectedRepartition;
            $expectedRepartitionTmp[array_keys($expectedRepartitionTmp)[$note - 1]] = 1;
            $onlyOneReviewExpectation["only one review with $note"] = [
                [new Review()->setRating($note)],
                $expectedRepartitionTmp
            ];
        }

        $noReview = [];
        $noReviewExpectation = [$noReview, $expectedRepartition];

        return [
            "many reviews" => $manyReviewsExpectation,
            "no review" => $noReviewExpectation,
            ...$onlyOneReviewExpectation,
            "two reviews" => [
                [
                    new Review()->setRating(1),
                    new Review()->setRating(2),
                ],
                array_merge($expectedRepartition, [
                    'numberOfOne' => 1,
                    'numberOfTwo' => 1,
                ]),
            ],

            "three reviews" => [
                [
                    new Review()->setRating(1),
                    new Review()->setRating(2),
                    new Review()->setRating(3),
                ],
                array_merge($expectedRepartition, [
                    'numberOfOne' => 1,
                    'numberOfTwo' => 1,
                    'numberOfThree' => 1,
                ]),
            ],
        ];
    }

    private function assertCountRatesPerValueEquals(
        $expected,
        NumberOfRatingPerValue $nbRatingsPerValue
    ) : void {
        self::assertTrue(
            $expected['numberOfOne'] === $nbRatingsPerValue->getNumberOfOne()
            && $expected['numberOfTwo'] === $nbRatingsPerValue->getNumberOfTwo()
            && $expected['numberOfThree'] === $nbRatingsPerValue->getNumberOfThree()
            && $expected['numberOfFour'] === $nbRatingsPerValue->getNumberOfFour()
            && $expected['numberOfFive'] === $nbRatingsPerValue->getNumberOfFive()
        );
    }
}
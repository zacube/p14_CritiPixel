<?php

namespace App\Tests\Rating;

use App\Model\Entity\Review;
use App\Model\Entity\VideoGame;
use App\Rating\RatingHandler;
use PHPUnit\Framework\TestCase;

// vérification du calcul de la moyenne des notes
class RatingHandlerTest extends TestCase
{
    private RatingHandler $ratingHandler;

    // SetUp appelé avant chaque test
    protected function setUp(): void
    {
        $this->ratingHandler = new RatingHandler();
    }

    /**
     * @return iterable<array{VideoGame, ?int}>
     */
    public static function provideVideoGame(): iterable
    {
        // cas n°1 : le VideoGame n'a aucune Review
        yield 'No review' => [new VideoGame(), null];

        // cas n°2 : le VideoGame a une seule Review
        yield 'One review' => [self::createVideoGame(5), 5];

        // cas n°3 : le VideoGame a 2 Review → calcule une moyenne juste (2+4/2)
        yield 'Two reviewsInt' => [self::createVideoGame(2, 4), 3];

        // cas n°4 : le VideoGame a 2 Review → calcule une moyenne avec arrondi
        yield 'Two reviewsFloat' => [self::createVideoGame(1, 4), 3];

        // cas n°5 : le VideoGame a plein de Review
        yield 'A lot of reviews' => [
            self::createVideoGame(1, 2, 2, 3, 3, 3, 4, 4, 4, 4, 5, 5, 5, 5, 5),
            4,
        ];
    }

    private static function createVideoGame(int ...$ratings): VideoGame
    {
        $videoGame = new VideoGame();

        foreach ($ratings as $rating) {
            $videoGame->getReviews()->add(new Review()->setRating($rating));
        }

        return $videoGame;
    }

    /**
     * @dataProvider provideVideoGame
     */
    public function testCalculateAverageRating(VideoGame $videoGame, ?int $expectedAverageRating): void
    {
        $this->ratingHandler->calculateAverage($videoGame);

        $this->assertSame($expectedAverageRating, $videoGame->getAverageRating());
    }

    /**
     * @return array<string, array<string, mixed>>
     **/
    public static function ratingProvider(): array
    {
        return [
            'cas n°1' => [
                'ratings' => [
                    'rating1' => [1, 4],
                    'rating2' => [2, 2],
                    'rating3' => [3, 0],
                    'rating4' => [4, 0],
                    'rating5' => [5, 1],
                ],
                'expected1' => 4,
                'expected2' => 2,
                'expected3' => 0,
                'expected4' => 0,
                'expected5' => 1,
            ],
            'cas n°2' => [
                'ratings' => [
                    'rating1' => [1, 5],
                    'rating2' => [2, 2],
                    'rating3' => [3, 2],
                    'rating4' => [4, 1],
                    'rating5' => [5, 1],
                ],
                'expected1' => 5,
                'expected2' => 2,
                'expected3' => 2,
                'expected4' => 1,
                'expected5' => 1,
            ],
        ];
    }

    // cas n°5 : comptage  - le VideoGame n'a aucune Review
    public function testCountRatingsWithNoReview(): void
    {
        $vg = new VideoGame();
        $this->ratingHandler->countRatingsPerValue($vg);
        // vérifie que lorsqu'on appelle la méthode avec un VideoGame sans Review, les méthodes suivantes retournent effectivement 0
        $this->assertSame(0, $vg->getNumberOfRatingsPerValue()->getNumberOfOne());
        $this->assertSame(0, $vg->getNumberOfRatingsPerValue()->getNumberOfTwo());
        $this->assertSame(0, $vg->getNumberOfRatingsPerValue()->getNumberOfThree());
        $this->assertSame(0, $vg->getNumberOfRatingsPerValue()->getNumberOfFour());
        $this->assertSame(0, $vg->getNumberOfRatingsPerValue()->getNumberOfFive());
    }

    /**
     * @dataProvider ratingProvider
     *
     * @param array<string, array<int>> $ratings
     */
    // cas n°6 : comptage - le VideoGame a plusieurs Review avec des notes différentes
    public function testCountRatingsWithReviews(array $ratings, int $expected1, int $expected2, int $expected3, int $expected4, int $expected5): void
    {
        $vg = new VideoGame();

        foreach ($ratings as $rating) {
            for ($i = 0; $i < $rating[1]; ++$i) {
                $vg->getReviews()->add($this->createReview($rating[0]));
            }
        }

        $this->ratingHandler->countRatingsPerValue($vg);
        // vérifie que, lorsqu'on appelle la méthode avec un VideoGame avec Review, les méthodes suivantes retournent le bon nombre de notes
        $this->assertSame($expected1, $vg->getNumberOfRatingsPerValue()->getNumberOfOne());
        $this->assertSame($expected2, $vg->getNumberOfRatingsPerValue()->getNumberOfTwo());
        $this->assertSame($expected3, $vg->getNumberOfRatingsPerValue()->getNumberOfThree());
        $this->assertSame($expected4, $vg->getNumberOfRatingsPerValue()->getNumberOfFour());
        $this->assertSame($expected5, $vg->getNumberOfRatingsPerValue()->getNumberOfFive());
    }

    private function createReview(int $rating): Review
    {
        $review = new Review();
        $review->setRating($rating);

        return $review;
    }
}

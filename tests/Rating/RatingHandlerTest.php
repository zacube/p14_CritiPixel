<?php
namespace App\Tests\Rating;
use App\Model\Entity\Review;
use App\Model\Entity\VideoGame;
use App\Model\Entity\NumberOfRatingPerValue;
use App\Rating\RatingHandler;
use PHPUnit\Framework\TestCase;
class RatingHandlerTest extends TestCase
{
    private RatingHandler $ratingHandler;

    // setUp étant appelé avant chaque test
    protected function setUp(): void
    {
        $this->ratingHandler = new RatingHandler();
    }

    private function createReview(int $rating): Review
    {
        $review = new Review();
        $review->setRating($rating);
        return $review;
    }

    public static function averageProvider(): array
    {
        return [
            [
                'rating1' => 2,
                'rating2' => 4,
                'expected' => 3
            ],
            [
                'rating1' => 1,
                'rating2' => 2,
                'expected' => 2
            ]
        ];
    }

    public static function ratingProvider(): array
    {
        return [
            'cas n°1' => [
                'ratings' => [
                    'rating1' => [1, 4],
                    'rating2' => [2, 2],
                    'rating3' => [3, 0],
                    'rating4' => [4, 0],
                    'rating5' => [5, 1]
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
                    'rating5' => [5, 1]
                ],
                'expected1' => 5,
                'expected2' => 2,
                'expected3' => 2,
                'expected4' => 1,
                'expected5' => 1,
            ],
            ];
    }


    public function testAverageWithNoReview()
    // cas n°1 : le VideoGame n'a pas de Review
    {
        //On crée un nouveau VideoGame. Reviews est défini dans le constructeur de VideoGame
        $vg= new Videogame;
        //On appelle la méthode
        $this->ratingHandler->calculateAverage($vg);
        //On vérifie que la méthode getAverageRating renvoie bien null
        $this->assertSame(null, $vg->getAverageRating());
    }

    /**
     * @dataProvider averageProvider
     */
    public function testAverageWithReviews(int $rating1, int $rating2, int $expected)
    // cas n°2 : calcule une moyenne juste (2+4/2)
    // cas n°3 : calcule une moyenne avec arrondi (1+2/2)
    {
        $vg = new VideoGame();
        $vg->getReviews()->add($this->createReview($rating1));
        $vg->getReviews()->add($this->createReview($rating2));
        $this->ratingHandler->calculateAverage($vg);
        //On vérifie que la méthode getAverageRating renvoie bien la valeur attendue
        $this->assertSame($expected, $vg->getAverageRating());
    }


    public function testCountRatingsWithNoReview()
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
     */
    public function testCountRatingsWithReviews(array $ratings, int $expected1, int $expected2, int $expected3, int $expected4, int $expected5)
    {
        $vg = new VideoGame();
        foreach ($ratings as $rating) {
            for ($i = 0; $i < $rating[1]; $i++) {
                $vg->getReviews()->add($this->createReview($rating[0]));
            }
        }

        $this->ratingHandler->countRatingsPerValue($vg);
        // vérifie que lorsqu'on appelle la méthode avec un VideoGame sans Review, les méthodes suivantes retournent effectivement 0
        $this->assertSame($expected1, $vg->getNumberOfRatingsPerValue()->getNumberOfOne());
        $this->assertSame($expected2, $vg->getNumberOfRatingsPerValue()->getNumberOfTwo());
        $this->assertSame($expected3, $vg->getNumberOfRatingsPerValue()->getNumberOfThree());
        $this->assertSame($expected4, $vg->getNumberOfRatingsPerValue()->getNumberOfFour());
        $this->assertSame($expected5, $vg->getNumberOfRatingsPerValue()->getNumberOfFive());
    }
}








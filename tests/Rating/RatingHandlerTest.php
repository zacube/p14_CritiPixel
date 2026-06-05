<?php
namespace App\Tests\Rating;
use App\Model\Entity\Review;
use App\Model\Entity\VideoGame;
use App\Rating\RatingHandler;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
class RatingHandlerTest extends TestCase
{
    private RatingHandler $ratingHandler;
    private ArrayCollection $reviews;

    // setUp étant appelé avant chaque test, la collection est réinitialisée à chaque fois
    protected function setUp(): void
    {
        $this->ratingHandler = new RatingHandler();
        $this->reviews = new ArrayCollection();
    }

    public static function ratingProvider(): array
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

    public function testAverageWithNoReview()
    // cas n°1 : le VideoGame n'a pas de Review
    {
        // crée un mock de VideoGame
        $vg = $this->getMockBuilder(VideoGame::class)->getMock();

        // renvoie la collection lorsque VideoGame->getReviews est appelé
        $vg
            ->expects($this->once())
            ->method('getReviews')
            ->willReturn($this->reviews);

        // vérifie que setAverageRating() est appelé exactement une fois avec la valeur demandée
        $vg
            ->expects($this->once())
            ->method('setAverageRating')
            ->with(null);

        // vérifie que le calcul donne la valeur attendue
        $this->ratingHandler->calculateAverage($vg);
    }

    /**
     * @dataProvider ratingProvider
     */
    public function testAverageWithReviews(int $rating1, int $rating2, int $expected)
    // cas n°2 : calcule une moyenne juste (2+4/2)
    // cas n°3 : calcule une moyenne avec arrondi (1+2/2)
    {
        //crée un premier mock pour Review
        $review1 = $this->getMockBuilder(Review::class)->getMock();
        $review1
            ->method('getRating')
            ->willReturn($rating1);
        //crée un deuxième mock pour Review
        $review2 = $this->getMockBuilder(Review::class)->getMock();
        $review2
            ->method('getRating')
            ->willReturn($rating2);
        // ajoute les deux mocks dans la collection
        $this->reviews->add($review1);
        $this->reviews->add($review2);

        // crée un mock de VideoGame
        $vg = $this->getMockBuilder(VideoGame::class)->getMock();
        // renvoie la collection lorsque VideoGame->getReviews est appelé
        $vg
            ->method('getReviews')
            ->willReturn($this->reviews);
        // vérifie que setAverageRating() est appelé exactement une fois avec la valeur demandée
        $vg
            ->expects($this->once())
            ->method('setAverageRating')
            ->with($expected);

        // vérifie que le calcul donne la valeur attendue
        $this->ratingHandler->calculateAverage($vg);
    }
}

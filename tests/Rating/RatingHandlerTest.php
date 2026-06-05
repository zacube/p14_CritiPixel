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

    protected function setUp(): void
    {
        $this->ratingHandler = new RatingHandler();
    }
    public function testAverageWithNoReview()
    {
        $reviews = new ArrayCollection();
        $vg = $this->getMockBuilder(VideoGame::class)->getMock();
        $vg
            ->expects($this->once())
            ->method('getReviews')
            ->willReturn($reviews);

        $vg
            ->expects($this->once())
            ->method('setAverageRating')
            ->with(null);

        $this->ratingHandler->calculateAverage($vg);
    }
    public function testAverageReviewScoreCalculation(){
        $reviews = new ArrayCollection();
        $review1 = $this->getMockBuilder(Review::class)->getMock();
        $review1
            ->method('getRating')
            ->willReturn(2);
        $review2 = $this->getMockBuilder(Review::class)->getMock();
        $review2
            ->method('getRating')
            ->willReturn(4);
        $reviews->add($review1);
        $reviews->add($review2);

        $vg = $this->getMockBuilder(VideoGame::class)->getMock();
        $vg
            ->method('getReviews')
            ->willReturn($reviews);
        $vg
            ->expects($this->once())
            ->method('setAverageRating')
            ->with(3);

        $this->ratingHandler->calculateAverage($vg);
    }
}

<?php
namespace App\Tests\Rating;
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
//public function testAverageReviewScoreCalculation()
}

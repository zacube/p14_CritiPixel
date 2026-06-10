<?php

namespace App\Tests\Model\Entity;

use App\Model\Entity\Review;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

class ReviewTest extends TestCase
{
    /**
     * @return iterable<string, array<int>>
     */
    public static function invalidRatingsProvider(): iterable
    {
        yield 'note trop basse' => [0];
        yield 'note trop haute' => [6];
    }

    /**
     * @dataProvider invalidRatingsProvider
     */
    public function testInvalidNotes(int $rating): void
    {
        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping() // pour lire #[Assert\...]
            ->getValidator(); // renvoie un objet Validator

        $review = new Review();
        $review->setRating($rating);

        $violations = $validator->validate($review);

        $this->assertCount(1, $violations); // on attend 1 erreur de validation
    }

    /**
     * @return iterable<string, array<int, int>>
     */
    public static function validRatingsProvider(): iterable
    {
        yield 'note ok1' => [1];
        yield 'note ok3' => [3];
        yield 'note ok5' => [5];
    }

    /**
     * @dataProvider validRatingsProvider
     */
    public function testValidNotes(int $rating): void
    {
        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        $review = new Review();
        $review->setRating($rating);

        $violations = $validator->validate($review);

        $this->assertCount(0, $violations); // on n'attend pas d'erreur de validation
    }
}
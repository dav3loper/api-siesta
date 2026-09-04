<?php

namespace Siesta\Rating\Domain;

use Siesta\Shared\Exception\ValueNotValid;

class RatingScore
{
    private const MIN = 1;
    private const MAX = 5;

    private int $score;

    /**
     * @throws ValueNotValid
     */
    public function __construct(int $score)
    {
        $this->validate($score);
        $this->score = $score;
    }

    /**
     * @throws ValueNotValid
     */
    private function validate(int $score): void
    {
        if ($score < self::MIN || $score > self::MAX) {
            throw new ValueNotValid();
        }
    }

    public function value(): int
    {
        return $this->score;
    }
}

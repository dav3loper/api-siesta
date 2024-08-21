<?php

namespace Siesta\Tests\Fixtures;

use Faker\Factory;
use Faker\Generator;

abstract class Mother
{
    protected Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create();
    }

    abstract public static function create(): Mother;

}
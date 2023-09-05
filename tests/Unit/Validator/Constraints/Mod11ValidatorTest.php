<?php

namespace App\Tests\Unit\Validator\Constraints;

use App\Validator\Constraints\Mod11Validator;
use PHPUnit\Framework\TestCase;

class Mod11ValidatorTest extends TestCase
{
    private Mod11Validator $mod11Validator;

    public function setUp(): void
    {
        parent::setUp();
        $this->mod11Validator = new Mod11Validator();
    }

    /**
     * @test
     * @dataProvider validateFindingRemainder_provider
     */
    public function validateFindingRemainder($value, $result): void
    {
        $this->assertEquals($result, $this->mod11Validator->validateFindingRemainder($value));
    }

    private function validateFindingRemainder_provider(): array
    {
        return [
            ["555X", 0, "Checksum is 10, so X is used. Value passes"],
            ["1120", 0, "Checksum is 11, so 0 is used. Value passes"],
            ["310", 0, "Checksum is 11, so 0 is used. Value passes"],
            ["311", 1, "Checksum not correct, as remainder is 1, not 0. Value fails"],
        ];
    }
}
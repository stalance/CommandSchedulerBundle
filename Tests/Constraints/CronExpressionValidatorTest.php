<?php

namespace JMose\CommandSchedulerBundle\Tests\Constraints;

use JetBrains\PhpStorm\Pure;
use JMose\CommandSchedulerBundle\Validator\Constraints\CronExpression;
use JMose\CommandSchedulerBundle\Validator\Constraints\CronExpressionValidator;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

/**
 * Class CronExpressionValidatorTest.
 */
class CronExpressionValidatorTest extends ConstraintValidatorTestCase
{
    #[Pure]
    protected function createValidator(): CronExpressionValidator
    {
        return new CronExpressionValidator();
    }

    /**
     * @dataProvider getValidValues
     * @param $value
     */
    public function testValidValues($value)
    {
        $this->validator->validate($value, new CronExpression(['message' => '']));

        $this->assertNoViolation();
    }

    public function getValidValues(): array
    {
        return [
            ['* * * * *'],
            ['@daily'],
            ['@yearly'],
            ['*/10 * * * *'],
        ];
    }

    /**
     * @dataProvider getInvalidValues
     * @param $value
     */
    public function testInvalidValues($value)
    {
        $constraint = new CronExpression(
            [
                'message' => 'myMessage',
            ]
        );

        $this->validator->validate($value, $constraint);

        $this->buildViolation('myMessage')
            ->assertRaised();
    }

    public function getInvalidValues(): array
    {
        return [
            ['*/10 * * *'],
            ['*/5 * * * ?'],
            ['sometimes'],
            ['never'],
            ['*****'],
            ['* * * * * * *'],
            ['* * * * * *'],
        ];
    }
}

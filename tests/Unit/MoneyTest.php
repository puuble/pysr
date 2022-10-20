<?php

namespace Tests\Unit;

use App\Http\Controllers\Money;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    /**
     * Test add
     *
     * @param App\Http\Controllers\Money $operandOne
     * @param App\Http\Controllers\Money $operandTwo
     * @param App\Http\Controllers\Money $expected
     *
     * @dataProvider addProvider
     */
    public function testAdd(Money $operandOne, Money $operandTwo, Money $expected)
    {

        $this->assertEquals($expected->ceil(), $operandOne->add($operandTwo));
    }

    /**
     * Add provider
     *
     * @return array
     */
    public function addProvider()
    {
        return array(
            array(new Money('2', 'EUR'), new Money('-1', 'EUR'), new Money('1', 'EUR')),
            array(new Money('-1', 'EUR'), new Money('-1', 'EUR'), new Money('-2', 'EUR')),
            array(new Money('1', 'USD'), new Money('-1', 'USD'), new Money('0', 'USD')),
            array(new Money('-1', 'RUB'), new Money('1', 'RUB'), new Money('0', 'RUB')),
            array(new Money('0.5', 'EUR'), new Money('0.5', 'EUR'), new Money('1', 'EUR')),
            array(new Money('-0.5', 'EUR'), new Money('-0.5', 'EUR'), new Money('-1', 'EUR')),
            array(new Money('-0.5', 'RUB'), new Money('0', 'RUB'), new Money('-0.5', 'RUB')),
            array(new Money('-0', 'USD'), new Money('0', 'USD'), new Money('0', 'USD')),
        );
    }
}

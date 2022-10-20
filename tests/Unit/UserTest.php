<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Http\Controllers\AccountType;
use App\Http\Controllers\Money;
use App\Http\Controllers\User;

class UserTest extends TestCase
{
    /**
     * Test Set Rule
     *
     * @param string $business
     * @param string $private
     *
     * @dataProvider setAccountTypeProvider
     */
    public function testSetAccountType($business, $private)
    {
        $user = new User();
        $this->assertEquals(AccountType::BUSINESS, $user->setAccountType($business));
        $this->assertEquals(AccountType::PRIVATE, $user->setAccountType($private));
    }
    /**
     * Test Set Remain
     *
     * @param App\Http\Controllers\Money $expected
     * @param $money
     *
     * @dataProvider setRemainProvider
     */
    public function testSetRemain($expected, Money $money)
    {
        $user = new User();
        $user->setAccountType('private');
        $this->assertEquals($expected, $user->setRemain($money));
    }
    /**
     * Test Set Remain with null 
     */
    public function testSetRemainWithNull()
    {
        $user = new User();

        $user->setAccountType('private');
        $this->assertEquals(new Money(1000, 'EUR'), $user->setRemain());

        $user->setAccountType('business'); // not valid free of charge up to 1000 EUR then set 0
        $this->assertEquals(new Money(0, 'EUR'), $user->setRemain());
    }
    /**
     * Test Set Remain with null 
     */
    public function testAddUser()
    {
        $user = new User();
        $result = [4, "private"];
        $user->addUser($result);
        $this->assertEquals($result[0], $user->id);
        $this->assertEquals($result[1], $user->accountType->value);
    }


    public function setRemainProvider()
    {
        return [
            [new Money(20, 'EUR'), new Money(20, 'EUR')],

        ];
    }
    public function setAccountTypeProvider()
    {

        return [
            ['business', 'private']
        ];
    }
}

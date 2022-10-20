<?php

namespace Tests\Unit;


use App\Http\Controllers\Commission;
use App\Http\Controllers\CommissionRule;
use App\Http\Controllers\User;
use PHPUnit\Framework\TestCase;

class CommissionTest extends TestCase
{
    /**
     * Test Set Rule
     *
     * @param string $deposit
     * @param string $withdraw
     *
     * @dataProvider setRuleProvider
     */
    public function testSetRule($deposit, $withdraw)
    {
        $commission = new Commission();
        $this->assertEquals(CommissionRule::DEPOSIT, $commission->setRule($deposit));
        $this->assertEquals(CommissionRule::WITHDRAW, $commission->setRule($withdraw));
    }
    /**
     * Test Set Comission
     *
     * @param string $type
     * @param string $accountType
     *
     * @dataProvider setCommissionProvider
     */
    public function testSetCommission($type, $accountType, $expected)
    {
        $commission = new Commission();
        $user = new User();
        $accountType = $user->setAccountType($accountType);
        $commission->setCommission($type, $accountType);
        $this->assertEquals($expected, $commission->getCommission());
    }

    /**
     * Test Get Comission
     *
     * @param float $expected
     * @param float $commission
     *
     * @dataProvider getCommissionProvider
     */
    public function testGetCommission($expected, $commission)
    {
        $this->assertEquals($expected, $commission);
    }

    public function setRuleProvider()
    {

        return [
            ['deposit', 'withdraw']
        ];
    }
    public function getCommissionProvider()
    {
        $commission = new Commission();
        $user = new User();

        return [
            [
                0.3, $commission->setCommission('withdraw', $user->setAccountType('private'))->getCommission(),
                0.03, $commission->setCommission('deposit', $user->setAccountType('private'))->getCommission(),

                0.5, $commission->setCommission('withdraw', $user->setAccountType('business'))->getCommission(),
                0.03, $commission->setCommission('deposit', $user->setAccountType('business'))->getCommission()
            ],


        ];
    }
    public function setCommissionProvider()
    {

        return [
            ['deposit', 'private', 0.03],
            ['withdraw', 'private', 0.3]
        ];
    }
}

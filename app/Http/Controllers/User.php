<?php

namespace App\Http\Controllers;

use Illuminate\Support\Arr;

enum AccountType: string
{
    case BUSINESS = "business";
    case PRIVATE = "private";
}

class User
{
    //
    const FREE_OF_CHARGE_AMOUNT = 1000;
    const FREE_OF_CHARGE_CURRENCY = 'EUR';

    public  $id;

    protected Money $remain;
    public  AccountType $accountType;


    /**
     * Calculate Remain for 1000.00 EUR per week.
     * 
     * @param Money|null $remain
     * 
     * @return Money
     */
    public function setRemain(Money $remain = null)
    {

        if ($remain == null) {
            if ($this->accountType == AccountType::PRIVATE) {
                $remain = new Money(self::FREE_OF_CHARGE_AMOUNT, self::FREE_OF_CHARGE_CURRENCY);
            } else {
                $remain = new Money(0, self::FREE_OF_CHARGE_CURRENCY);
            }
        }

        $this->remain = $remain;
        return $remain;
    }

    /**
     * Set Account type private or bussines 
     * 
     * @param string $accountType
     * 
     * @return enum AccountType
     */
    public function setAccountType($accountType)
    {
        $this->accountType = AccountType::from($accountType);

        return $this->accountType;
    }

    /**
     * Get Remain 
     * 
     * @return Money $remain
     */
    public function getRemain()
    {
        return $this->remain;
    }

    public function removeDate($data)
    {
        $result = (Arr::except($data, current(array_keys($data)))); // delete date row "2014-12-31" no need this
        return $result;
    }

    /**
     * Add User with data
     * 
     * @param array $result
     * 
     * @return static
     */
    public function addUser($result)
    {

        list($userId, $accountType) = array_values($result);

        $this->id = $userId;
        $this->accountType = AccountType::from($accountType);


        return $this;
    }
}

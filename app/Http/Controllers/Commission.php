<?php

namespace App\Http\Controllers;




enum CommissionRule: String
{
    case DEPOSIT = "deposit";
    case WITHDRAW = "withdraw";

    public function getWitdrawCom(AccountType $accountType): float
    {
        return match ($this) {
            static::WITHDRAW => match ($accountType) {
                AccountType::BUSINESS => 0.5,
                AccountType::PRIVATE => 0.3
            },
            static::DEPOSIT => 0.03
        };
    }
}

class Commission
{


    //
    protected $commission;

    protected $acountType;

    public $rule;


    /**
     * Set Commission Rule 
     * 
     * @param string $type
     * 
     * @return enum CommissionRule
     */
    public function setRule($type)
    {
        $this->rule = CommissionRule::from($type);

        return $this->rule;
    }



    /**
     * Set Commission
     * 
     * @param string $rule
     * @param AccountType $accountType
     * 
     * @return static 
     */
    public function setCommission($rule, $accountType)
    {
        $com =  CommissionRule::from($rule) ?? null;

        if ($com) {
            $accountType = $accountType ?? null;
            if ($accountType) {
                $com = $com->getWitdrawCom($accountType);
            }
        }

        if (!is_float($com)) {
            throw new \Exception("Commission must be float number", 1);
        }

        $this->commission = floatval($com);
        return $this;
    }

    /**
     * Get Comission calculate by account and transaction type 
     * 
     * @return float $comission;
     */
    public function getCommission()
    {
        return floatval($this->commission);
    }
}

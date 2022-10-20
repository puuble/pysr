<?php

namespace App\Http\Controllers;

use Carbon\Carbon;

class Transaction
{

    const CURRENCY = 'EUR';
    const FREE_OF_CHARGE_COUNT = 3;

    protected $transactions;
    protected $exchange;



    public function __construct()
    {
        $this->exchange = new Exchange();
    }

    /**
     * Set Transactions by grouped user;
     * 
     * @param array $transaction
     * @param string|integer $userID
     * 
     * @return array 
     */
    public function setTransactions($transaction, $userId)
    {
        $this->transactions[$userId][] = $transaction;
        return $this->transactions[$userId];
    }

    /**
     * Get Transactions with userId
     * 
     * @param stirng|integer $userId
     * 
     * @return array
     */
    public function getTransactions($userId = null)
    {
        if (!is_null($userId)) {
            return $this->transactions[$userId];
        }

        return $this->transactions;
    }

    /**
     * Get Exchange information from Exchange Class
     * 
     * @param text|integer $amount
     * @param text|integer $currency
     * 
     * @return Money
     */
    public function getExchangeCurrency($amount, $currency)
    {
        $exchangeCurency = $this->exchange->setExchange(new Money($amount, $currency));
        return $exchangeCurency->getExchange();
    }

    /**
     * Set Default Fee on Money
     * 
     * @param Money $fee
     * 
     * @return Money $fee
     */
    public function setFee(Money $fee = null)
    {
        if (is_null($fee)) {
            $fee = new Money(0, self::CURRENCY);
        }

        return $fee;
    }

    /**
     * Deposit starting process when get type = deposit on csv
     * 
     * @param User $user;
     * 
     * @param array $result;
     * 
     * @return array $result;
     */
    public function deposit(User $user, $result)
    {
        $amount = $result['amount'];
        $currency = $result['currency'];
        $transactionAmount = $this->getExchangeCurrency($amount, $currency);
        $amount = $transactionAmount->getAmount();
        $currency = $transactionAmount->getCurrency();
        $result['exchangedAmount'] = $amount;
        $lastTransaction = $this->getLastTransaction($user->id);
        $com = $result['commission'];
        $remain =  $transactionAmount->createZero($transactionAmount->getCurrency());
        $result['fee'] = $transactionAmount->commissionFee($com);
        $result['remain'] = $remain;
        $result['freeOfChargeCount'] = !is_null($lastTransaction) ? $lastTransaction['freeOfChargeCount'] : self::FREE_OF_CHARGE_COUNT;
        $result['type'] = CommissionRule::DEPOSIT;

        return $result;
    }

    /**
     * Withdraw starting process when get type = withdraw on csv
     * 
     * @param User $user;
     * 
     * @param array $result;
     * 
     * @return array $result;
     */
    public function withdraw(User $user, $result)
    {
        $lastTransaction = $this->getLastTransaction($user->id);
        $freeOfChargeCount = $this->getCountPerWeek($user->id, $result);
        $remain = $freeOfChargeCount == self::FREE_OF_CHARGE_COUNT ? $user->setRemain() : $user->getRemain();
        $amount = $result['amount'];
        $currency = $result['currency'];
        $transactionAmount = $this->getExchangeCurrency($amount, $currency);
        $amount = $transactionAmount->getAmount();
        $currency = $transactionAmount->getCurrency();
        $com = $result['commission'];
        $fee = $this->setFee();
        $result['exchangedAmount'] = $amount;
        $result['type'] = CommissionRule::WITHDRAW;

        if ($lastTransaction == null) {
            if ($transactionAmount->isLte($remain)) {
                $remain = $remain->sub($transactionAmount);
            } else {
                $remain = $transactionAmount->sub($remain);
                $fee =  $remain->commissionFee($com);
                $remain = $user->getRemain()->createZero($currency);
                $user->setRemain($remain);
                $freeOfChargeCount = 0;
            }


            $result['fee'] = $fee;
            $result['remain'] = $remain;
            $result['freeOfChargeCount'] = $freeOfChargeCount;
        } else {

            if ($freeOfChargeCount <= 0 || $remain->isZero()) {
                $fee = $transactionAmount->commissionFee($com);
            } else {
                if ($transactionAmount->isLte($remain)) {
                    $remain = $remain->sub($transactionAmount);
                    $user->setRemain($remain);
                } else {
                    $remain =  $transactionAmount->sub($remain);
                    $fee = $remain->commissionFee($com);
                    $remain = $user->getRemain()->createZero($currency);
                    $user->setRemain($remain);
                }
            }
            $result['fee'] = $fee;
            $result['remain'] = $remain;
            $result['freeOfChargeCount'] = $freeOfChargeCount;
        }

        return $result;
    }

    /**
     * Pushing All Transactions to array with userId Key for grouping;
     * 
     * @param string|integer $userId 
     * 
     * @return array $transactions;
     */
    public function getLastTransaction($userId)
    {
        if (!isset($this->transactions[$userId])) {
            return null;
        }
        return end($this->transactions[$userId]);
    }

    /**
     * Check transaction in a week for free of charges. Count 3 times per week by rule self::FREE_OF_CHARGE_COUNT;
     * 
     * @param string|integer $userId 
     * @param integer $count 
     * 
     * @return array $transactions;
     */
    public function getCountPerWeek($userId, $result)
    {

        $count = self::FREE_OF_CHARGE_COUNT;
        $date = $result['date'];
        if (!isset($this->transactions[$userId])) {
            return $count;
        } else {
            $lastTransaction = $this->getLastTransaction($userId);

            if (!is_null($lastTransaction)) {
                $date = Carbon::parse($date);
                $lastTransactionDate = $lastTransaction['date'];
                $lastTransactionDate =  Carbon::parse($lastTransactionDate);

                if ($date->startOfWeek() == $lastTransactionDate->startOfWeek()) {
                    $count = $lastTransaction['freeOfChargeCount'];
                    if ($lastTransaction['type'] == CommissionRule::WITHDRAW) {
                        $count--;
                    }
                } else {
                    $count =  self::FREE_OF_CHARGE_COUNT;
                }
            } else {
                if ($lastTransaction['type'] == CommissionRule::WITHDRAW) {
                    $count--;
                }
            }
        }

        return $count;
    }
}

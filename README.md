# pysr

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

-   [Simple, fast routing engine](https://laravel.com/docs/routing).
-   [Powerful dependency injection container](https://laravel.com/docs/container).
-   Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
-   Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
-   Database agnostic [schema migrations](https://laravel.com/docs/migrations).
-   [Robust background job processing](https://laravel.com/docs/queues).
-   [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

I tried to do as much as I understood from the Assignment given. I thought it was suitable for me as I have previous experience in crypto.

I start the transactions according to the EUR parity,

```php
#/app/Http/Controllers/Exchange.php
const DEFAULT_CURRENCY = "EUR";
```

I aggregated transactions by USER ID and did a date comparison in CSV between the previous row and the row I got.

```json
namespace App\Http\Controllers;
```

```php
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
```

I count down from 3 after making sure that the transaction dates start in the same week, from Monday to Sunday. And I only do this for the Withdrawal operations.
If two transactions are not in the same week, I start counting again from 3.
Thus, users can withdraw money up to 1000.00 EUR 3 times a week for free.
I reset the countdown when the rights expire or exceed the 1000.00 Euro limit. and I take commission on withdrawals that occur during that week.

```php
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
```

get withdraw comission by account type

```sh
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
```

I Use 3rd party lib for Money Class

```sh
    use Maba\Component\Monetary\MoneyInterface;
    use Maba\Component\Math\BcMath;
    use Maba\Component\Math\Math;
```

## Tech

PHP 8.0^
https://laravel.com/docs/9.x/artisan

## Installation

Install the dependencies and devDependencies and start the server.

```sh
cd pysr
composer install
php artisan calculate:fee input.csv
```

for test

```sh
./vendor/bin/phpunit
```

## Plugins

Dillinger is currently extended with the following plugins.
Instructions on how to use them in your own application are linked below.

| Plugin            |
| ----------------- |
| maba/monetary     |
| laravel/framework |

MIT

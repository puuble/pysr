<?php

namespace App\Console\Commands;

use App\Http\Controllers\AccountType;
use App\Http\Controllers\Commission;
use App\Http\Controllers\CommissionRule;
use App\Http\Controllers\Exchange;
use App\Http\Controllers\Money;
use App\Http\Controllers\Transaction;
use App\Http\Controllers\User;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class CalculateFee extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calculate:fee {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Commission fee calculation: Commission fee is always calculated in the currency of the operation. For example, if you withdraw or deposit in US dollars then commission fee is also in US dollars.';
    public function csvToArray($file, $delimiter)
    {
        if (($handle = fopen($file, 'r')) !== FALSE) {

            $i = 0;
            while (($lineArray = fgetcsv($handle, 4000, $delimiter, '"')) !== FALSE) {
                if (count($lineArray) == 1 && is_null($lineArray[0])) {
                    continue;
                }
                for ($j = 0; $j < count($lineArray); $j++) {
                    $arr[$i][$j] = trim($lineArray[$j]);
                }
                $i++;
            }
            fclose($handle);
        }
        return $arr;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            //code...

            $filename = $this->argument('file');
            $calculate = new Commission();
            $user = new User();
            $transaction = new Transaction();
            $arr = $this->csvToArray($filename, ',');
            if (!is_array($arr)) {
                $this->error('Check File format');
            }
            foreach ($arr as $data) {
                list($date, $userId, $accountType, $type, $amount, $currency, $excepted) = $data;
                $result = $user->removeDate($data); // delete date row "2014-12-31" no need this
                $user->addUser($result);
                $accountType = $user->accountType;
                $calculate->setCommission($type, $accountType);
                $rule =  $calculate->setRule($type);

                $result = [
                    'date' => $date,
                    'amount' => $amount,
                    'currency' => $currency,
                    'commission' => $calculate->getCommission(),
                ];

                if ($rule == CommissionRule::WITHDRAW) {
                    $result =   $transaction->withdraw($user, $result);
                } else if ($rule == CommissionRule::DEPOSIT) {
                    $result =  $transaction->deposit($user, $result);
                }
                $transaction->setTransactions($result, $user->id);

                $lines = [
                    $result['fee']->round()->formatAmount(),
                    $excepted,
                    $date,
                    $userId,
                    $type,
                    $result['freeOfChargeCount'],
                    $result['remain']->formatAmount(),

                    $result['exchangedAmount'],
                    $amount,
                    $currency

                ];

                $this->line(implode('-', $lines));
            }


            //dd($transaction->getTransactions());

            return Command::SUCCESS;
        } catch (\Throwable $th) {
            //throw $th;
            $this->error($th->getMessage());
        }
    }
}

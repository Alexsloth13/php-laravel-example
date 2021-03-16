<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UnitPayModel extends Model
{
    protected $table = 'unitpay_payments';

    public function getFormSignature($account, $currency, $desc, $sum, $secretKey) {
        $hashStr = $account.'{up}'.$currency.'{up}'.$desc.'{up}'.$sum.'{up}'.$secretKey;
        return hash('sha256', $hashStr);
    }

    public function defineRealIP(){
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            return $_SERVER['HTTP_CF_CONNECTING_IP'];
        }elseif(!empty($_SERVER['HTTP_X_REAL_IP'])) {
            return $_SERVER['HTTP_X_REAL_IP'];
        }elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }else{
            return $_SERVER['REMOTE_ADDR'];
        }
    }

    public function unitpayAccessIP($requestIP)
    {
        $unitpayAccessIp = Array(
            '31.186.100.49',
            '178.132.203.105',
            '52.29.152.23',
            '52.19.56.234'
        );

        return in_array($requestIP, $unitpayAccessIp);
    }

    public function createPayment($unitpayId, $account, $sum)
    {
        return DB::table($this->table)->insert([
			'unitpayId' => $unitpayId, 
			'account' 	=> $account,
			'sum' 		=> $sum,
			'dateCreate' => Carbon::now(),
			'status' 	=> 0
		]);
    }

    public function getPaymentByUnitpayId($unitpayId)
    {
        return DB::table($this->table)
		->select('*')
		->where('unitpayId', $unitpayId)
		->limit(1)
        ->exists();
    }

    public function getPaymentStatusByUnitpayId($unitpayId)
    {
        return DB::table($this->table)
		->select('status')
		->where('unitpayId', $unitpayId)
		->limit(1)
        ->value('status');
    }

    public function confirmPaymentByUnitpayId($unitpayId)
    {      
        return DB::table($this->table)
        ->where('unitpayId', $unitpayId)
        ->limit(1)
        ->update([
            'status' => 1,
            'dateComplete' => Carbon::now()]);   
    }

    public function getAccountByName($account)
    {
         return DB::table('users')
		->select('*')
		->where('email', $account)
		->limit(1)
		->exists();
    }

    public function donateForAccount($account, $count)
    {   return DB::table('users')
        ->where('email', $account)
        ->increment('balance', $count);
    }

    public function getSha256SignatureByMethodAndParams($method, array $params, $secretKey)
    {
        ksort($params);
        unset($params['sign']);
        unset($params['signature']);
        array_push($params, $secretKey);
        array_unshift($params, $method);

        return hash('sha256', join('{up}', $params));
    }

    public function getSignature($account, $currency, $desc, $sum, $secretKey)
    {
        $hashStr = $account.'{up}'.$currency.'{up}'.$desc.'{up}'.$sum.'{up}'.$secretKey;
        return hash('sha256', $hashStr);
    }

    public function responseFail($message)
    {
        return Array(
            'error' => Array(
                'message' 	=> $message
            )
        );
    }

    public function responseSuccess($message)
    {
        return Array(
            'result' => Array(
                'message' 	=> $message
            )
        );
    }
}

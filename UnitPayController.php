<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UnitPayModel;

class UnitPayController extends Controller
{
    public function fail(Request $request)
    {        
        return redirect('payment')->with('status', "Оплата не прошла");
    }

    public function success(Request $request)
    {
        return redirect('payment')->with('success', "Оплата прошла успешно");
    }

    public function api(Request $request, UnitPayModel $pay)
    {
        $secretKey = '12ew1c8fbc88bac9975ef77722a222qw';

        $requestIP = $pay->defineRealIP();

        // Проверяем ip серверов UnitPay
        if(!$pay->unitpayAccessIP($requestIP)){
            return response()->json(
                $pay->responseFail("Invalid IP")
            );
        }

        // Проверяем корректность запроса
        if (empty($request->method) || empty($request->params) || !is_array($request->params)){
            return response()->json(
                $pay->responseFail("Invalid request")
            );
        }

        // Проверяем цифровую подпись
        if ($request->params['signature'] !== $pay->getSha256SignatureByMethodAndParams($request->method, $_GET['params'], $secretKey)){
            return response()->json(
                $pay->responseFail("Incorrect digital signature")
            );
        }
        
        // Проверяем существует ли пользователь в системе
        if (!$pay->getAccountByName($request->params['account'])) {
            return response()->json(
                $pay->responseFail("User is not found")
            );
        }

        // Проверяем валюту платежа RUB/USD
        if($request->params['orderCurrency'] !== 'USD'){
            return response()->json(
                $pay->responseFail("Incorrect payment currency")
            );
        }

        if($request->method === 'check'){

            // Проверяем наличие дубля платежа
            if ($pay->getPaymentByUnitpayId($request->params['unitpayId'])){
                return response()->json(
                    $pay->responseFail("Payment already exists")
                );
            }

            // Проверяем создан ли платеж в системе
            if (!$pay->createPayment($request->params['unitpayId'], $request->params['account'], $request->params['orderSum'])){ 
                return response()->json(
                    $pay->responseFail("Unable to create payment database")
                );
            }else{
                return response()->json(
                    $pay->responseSuccess("CHECK is successful")
                );
            }
        }

        if ($request->method === 'pay'){

            $payment = $pay->getPaymentByUnitpayId($request->params['unitpayId']);
            $payment_status =$pay->getPaymentStatusByUnitpayId($request->params['unitpayId']);
            // Платеж уже оплачен
            if ($payment && $payment_status === 1){
                return response()->json(
                    $pay->responseSuccess("Payment has already been paid")
                );
            }

            // Подтверждаем платеж
            if (!$pay->confirmPaymentByUnitpayId($request->params['unitpayId'])){
                return response()->json(
                    $pay->responseFail("Unable to confirm payment database")
                );
            }
            
            // Зачисляем деньги на баланс пользователя
            if($pay->donateForAccount($request->params['account'], $request->params['orderSum'])){
                return response()->json(
                    $pay->responseSuccess("PAY is successful")
                );
            }
        }

        // Метод не поддерживается
        return response()->json(
            $pay->responseFail($request->method.' not supported')
        );
    }
}

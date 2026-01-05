<?php

namespace App\Http\Services\Payment;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CcAvenueService
{
    private $merchantId = 53991;
    private $accessCode = "AVIZ05MC31CG31ZIGC";
    private $workingKey = "EC0F8F6DDA15DC0535428D58DF3396AF";

    public function __construct()
    {
        //
    }

    private function encryptData($merchant_data)
    {
        return encrypt($merchant_data, $this->workingKey); // Method for encrypting the data.
    }
    private function merchantDataCreation($request)
    {
        $merchant_data = "";
        foreach ($request as $key => $value) {
            $merchant_data .= $key . '=' . $value . '&';
        }

        $merchant_data .= "merchant_id=" . $this->merchantId;
        return $this->encryptData($merchant_data);
    }

    public function generatePaymentUrl($request)
    {
        $merchant_data = $this->merchantDataCreation($request);
        $response = Http::post('https://login.ccavenue.ae/apis/servlet/DoWebTrans', [
            'enc_request' => $merchant_data,
            'access_code' => $this->accessCode,
            'request_type' => 'JSON',
            'response_type' => 'JSON',
            'command' => 'generateQuickInvoice',
            'version' => '1.1',
            'customer_name' => 'jesvin',
            'customer_email_id' => 'jesvin@logiclabz.co.in',
            'customer_email_subject' => 'Invoice',
            'customer_mobile_number' => '9876543210',
            'valid_for' => '2',
            'valid_type' => 'days',
            'Currency' => 'AED',
            'amount' => '500.00',
            'bill_delivery_type' => 'NONE',
        ]);
        dd($response->json());
    }
}
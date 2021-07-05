<?php

class GumPayApp2AppHelper
{
    public const GUMPAY_ENVIRONMENT_URL = 'https://api.gumpay.app/';

    /// <summary>
    /// GetOrderLink return a GumPay url that allow user to pay our order. This url can be used in Android/IOS and it will launch GumPay app if it is installed or open a landing page where user can download app and process payment
    /// </summary>
    /// <param name="uniqueKey">Shop unique apikey provided by GumPay Team</param>
    /// <param name="externalOrderId">The order Id in our Shop system, it shoudl be unique to identify this order and need be used later again to retrieve payment status</param>
    /// <param name="amount">The total amount of the order to be paid</param>
    /// <param name="returnUrl">This is the callback url that GumPay app will call once payment completed. It can be our deep app link url like in this example, or some backend url where we will check the payment. We recomend to include in this url the order id in the Shop to make it easy identify and verify the status of the transaction</param>
    /// <param name="minutesToExpire">This is the minutes that link/qr will be valid</param>
    /// <returns>Returns string containing the url we need redirect user to</returns>
    public function GetOrderLink($uniqueKey, $externalOrderId, $amount, $returnUrl, $minutesToExpire)
    {
        $endpointUrl = $this::GUMPAY_ENVIRONMENT_URL . "api/order/getorderlink";
        $curl = new \CurlPost($endpointUrl);
        // execute the request
        $responseStr = $curl(array(
            "uniqueKey" => $uniqueKey,
            "externalOrderId" => $externalOrderId,
            "amount" => $amount,
            "returnUrl" => $returnUrl,
            "minutesToExpire" => $minutesToExpire,
        ));
        $response = json_decode($responseStr);
        if ($response->Success)
        {
            return $response->Data;
        }
        else
        {
            throw new \Exception($response != null ? $response->StatusMessage : "Comunication with server failed, please try again");
        }
    }
     /// <summary>
    /// GetOrderQR return a base64 encoded image that allow user to pay our order. This url can be used in Android/IOS and it will launch GumPay app if it is installed or open a landing page where user can download app and process payment
    /// </summary>
    /// <param name="uniqueKey">Shop unique apikey provided by GumPay Team</param>
    /// <param name="externalOrderId">The order Id in our Shop system, it shoudl be unique to identify this order and need be used later again to retrieve payment status</param>
    /// <param name="amount">The total amount of the order to be paid</param>
    /// <param name="returnUrl">This is the callback url that GumPay app will call once payment completed. It can be our deep app link url like in this example, or some backend url where we will check the payment. We recomend to include in this url the order id in the Shop to make it easy identify and verify the status of the transaction</param>
    /// <param name="minutesToExpire">This is the minutes that link/qr will be valid</param>
    /// <returns>Returns string containing the url we need redirect user to</returns>
    public function GetOrderQR($uniqueKey, $externalOrderId, $amount, $returnUrl, $minutesToExpire)
    {
        $endpointUrl = $this::GUMPAY_ENVIRONMENT_URL . "api/order/getorderqr";
        $curl = new \CurlPost($endpointUrl);
        // execute the request
        $responseStr = $curl(array(
            "uniqueKey" => $uniqueKey,
            "externalOrderId" => $externalOrderId,
            "amount" => $amount,
            "returnUrl" => $returnUrl,
            "minutesToExpire" => $minutesToExpire,
        ));
        $response = json_decode($responseStr);
        if ($response->Success)
        {
            return $response->Data;
        }
        else
        {
            throw new \Exception($response != null ? $response->StatusMessage : "Comunication with server failed, please try again");
        }
    }
    /// <summary>
    /// CheckOrderComplete need be used to check if Shop order was already paid or not in GumPay
    /// </summary>
    /// <param name="uniqueKey">Shop unique apikey provided by GumPay Team</param>
    /// <param name="externalOrderId">The order Id in our Shop system. It shoudl be the same order id we sent in the previous GetOrderLink request</param>
    /// <returns>It returns the GumPay transactionId if it was succesfully paid. It returns an empty Guid if the transaction was not paid 00000000-0000-0000-0000-000000000000</returns>
    public function CheckOrderComplete($uniqueKey, $externalOrderId)
    {
        $endpointUrl = $this::GUMPAY_ENVIRONMENT_URL . "api/order/checkordercomplete";
        $curl = new \CurlPost($endpointUrl);
        // execute the request
        $responseStr = $curl(array(
            "uniqueKey" => $uniqueKey,
            "externalOrderId" => $externalOrderId,
        ));
        $response = json_decode($responseStr);
        if ($response->Success)
        {
            if(isset($response->Data))
            {
                return $response->Data;
            }
            else
            {
                return false;
            }
        }
        else
        {
            throw new \Exception($response != null ? $response->StatusMessage : "Comunication with server failed, please try again");
        }
    }

    /// <summary>
    /// GetPreauthLink return a GumPay url that allow user to authorize automatic payments on current shop. This url can be used in Android/IOS and it will launch GumPay app if it is installed or open a landing page where user can download app and authorize payments
    /// </summary>
    /// <param name="uniqueKey">Shop unique apikey provided by GumPay Team</param>
    /// <param name="returnUrl">This is the callback url that GumPay app will call once preauth completed. It can be our deep app link url like in this example, or some backend url where we will check the payment. We recomend will return in this url a parameter "userid" that will containe the GumPay userid that preautorized payments for your shop</param>
    /// <param name="minutesToExpire">This is the minutes that link/qr will be valid</param>
    /// <returns>Returns string containing the url we need redirect user to</returns>
    public function GetPreauthLink($uniqueKey, $returnUrl, $minutesToExpire)
    {
        $endpointUrl = $this::GUMPAY_ENVIRONMENT_URL . "api/order/getpreauthlink";
        $curl = new \CurlPost($endpointUrl);
    
        // execute the request
        $responseStr = $curl(array(
            "uniqueKey" => $uniqueKey,
            "returnUrl" => $returnUrl,
            "minutesToExpire" => $minutesToExpire,
        ));
        $response = json_decode($responseStr);
        if ($response->Success)
        {
            return $response->Data;
        }
        else
        {
            throw new \Exception($response != null ? $response->StatusMessage : "Comunication with server failed, please try again");
        }
    }
    /// <summary>
    /// PreauthorizeTransaction
    /// </summary>
    /// <param name="uniqueKey">Shop unique apikey provided by GumPay Team</param>
    /// <param name="userId"></param>
    /// <param name="currency">For now, only 'HKD' supported</param>
    /// <param name="amount"></param>
    /// <returns>Returns string containing the token for the transaction</returns>
    public function PreauthorizeTransaction($uniqueKey, $userId, $currency, $amount)
    {
        $endpointUrl = $this::GUMPAY_ENVIRONMENT_URL . "api/order/preauthorizetransaction";
        $curl = new \CurlPost($endpointUrl);
        // execute the request
        $responseStr = $curl(array(
            "uniqueKey" => $uniqueKey,
            "userId" => $userId,
            "currency" => $currency,
            "amount" => $amount,
        ));
        $response = json_decode($responseStr);
        if ($response->Success)
        {
            return $response->Data;
        }
        else
        {
            throw new \Exception($response != null ? $response->StatusMessage : "Comunication with server failed, please try again");
        }
    }
    /// <summary>
    /// PreauthorizeTransaction
    /// </summary>
    /// <param name="uniqueKey">Shop unique apikey provided by GumPay Team</param>
    /// <param name="userId"></param>
    /// <param name="token"></param>
    /// <param name="currency">For now, only 'HKD' supported</param>
    /// <param name="amount"></param>
    /// <returns>Returns string containing the token for the transaction</returns>
    public function CaptureTransaction($uniqueKey, $userId, $token, $currency, $amount)
    {
        $endpointUrl = $this::GUMPAY_ENVIRONMENT_URL . "api/order/capturetransaction";
        $curl = new \CurlPost($endpointUrl);
        // execute the request
        $responseStr = $curl(array(
            "uniqueKey" => $uniqueKey,
            "userId" => $userId,
            "token" => $token,
            "currency" => $currency,
            "amount" => $amount,
        ));
        $response = json_decode($responseStr);
        if ($response->Success)
        {
            return $response->Data;
        }
        else
        {
            throw new \Exception($response != null ? $response->StatusMessage : "Comunication with server failed, please try again");
        }
    }
     /// <summary>
    /// CancelTransaction
    /// </summary>
    /// <param name="uniqueKey">Shop unique apikey provided by GumPay Team</param>
    /// <param name="userId"></param>
    /// <param name="token"></param>
    /// <returns>Returns string containing the token for the transaction</returns>
    public function CancelTransaction($uniqueKey, $userId, $token)
    {
        $endpointUrl = $this::GUMPAY_ENVIRONMENT_URL . "api/order/canceltransaction";
        $curl = new \CurlPost($endpointUrl);
        // execute the request
        $responseStr = $curl(array(
            "uniqueKey" => $uniqueKey,
            "userId" => $userId,
            "token" => $token,
        ));
        $response = json_decode($responseStr);
        if ($response->Success)
        {
            return $response->Data;
        }
        else
        {
            throw new \Exception($response != null ? $response->StatusMessage : "Comunication with server failed, please try again");
        }
    }
    /// <summary>
    /// GetPreauthorizedUsers
    /// </summary>
    /// <param name="uniqueKey">Shop unique apikey provided by GumPay Team</param>
    /// <returns>Returns list of preauthorized userIds</returns>
    public function GetPreauthorizedUsers($uniqueKey)
    {
        $endpointUrl = $this::GUMPAY_ENVIRONMENT_URL . "api/order/getpreauthorizedusers";
        $curl = new \CurlPost($endpointUrl);
        // execute the request
        $responseStr = $curl(array(
            "uniqueKey" => $uniqueKey,
        ));
        $response = json_decode($responseStr);
        if ($response->Success)
        {
            return $response->Data;
        }
        else
        {
            throw new \Exception($response != null ? $response->StatusMessage : "Comunication with server failed, please try again");
        }
    }
}
class CurlPost
{
    private $url;
    private $options;
           
    /**
     * @param string $url     Request URL
     * @param array  $options cURL options
     */
    public function __construct($url, array $options = [])
    {
        $this->url = $url;
        $this->options = $options;
    }

    /**
     * Get the response
     * @return string
     * @throws \RuntimeException On cURL error
     */
    public function __invoke(array $post)
    {
        $ch = \curl_init($this->url);
        
        foreach ($this->options as $key => $val) {
            \curl_setopt($ch, $key, $val);
        }

        \curl_setopt($ch, \CURLOPT_RETURNTRANSFER, true);
        \curl_setopt($ch, \CURLOPT_POSTFIELDS, $post);
        \curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = \curl_exec($ch);
        $error    = \curl_error($ch);
        $errno    = \curl_errno($ch);
        
        if (\is_resource($ch)) {
            \curl_close($ch);
        }

        if (0 !== $errno) {
            throw new \RuntimeException($error, $errno);
        }
        
        return $response;
    }
}
    
<?php

require_once 'GumPayApp2AppHelper.php';

const UniqueKey = "cGwkQgH4TXofJFplxDAFe0PUGWaCZo0IFnIQMncwuBh1VnLQRhwmWHujaxU95sBYGftC7bMoeqjRPVwQEKf6gpOr0RMYuLAsfN4Ax7g9ukjDRgJyRVh8VwCBYCh7QRl2";

if(isset($_POST['action']))
{
    switch($_POST['action'])
    {
        case 'getqr':
            {
                $gumpayHelper = new GumPayApp2AppHelper();
                $url = $gumpayHelper->GetOrderQR(UniqueKey, $_POST['order'], $_POST['amount'], $_POST['returnUrl']);
                header('Content-type: application/json');
                echo json_encode(array("qrimage" => $url));
                break;
            }
        case 'checkpayment':
            {
                $gumpayHelper = new GumPayApp2AppHelper();
                $result = $gumpayHelper->CheckOrderComplete(UniqueKey, $_POST['order']);
                if($result)
                {
                    header('Content-type: application/json');
                    echo json_encode(array("result"=> true, "transactionId"=> $result));
                }
                else
                {
                    header('Content-type: application/json');
                    echo json_encode(array("result"=> false));
                }
                break;
            }
    }
}
<?php

require_once 'GumPayApp2AppHelper.php';

const UniqueKey = "ahqQ5TbibZLRGm8tmGCiHymDWqGAiEdh0Rz1JczHWYs4eCrJbPt7Iv2Db4Vlz20GF7oiNCum0hrsAf00iO3lSNw0cBFXdTUi1HueBQGYHEhxe4jHcysFLVH4GRR9pqol";

if(isset($_POST['action']))
{
    switch($_POST['action'])
    {
        case 'getqr':
            {
                $gumpayHelper = new GumPayApp2AppHelper();
                $url = $gumpayHelper->GetOrderQR(UniqueKey, $_POST['order'], $_POST['amount'], $_POST['returnUrl'], 60);
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
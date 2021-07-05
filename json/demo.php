<?php

require_once 'GumPayApp2AppHelper.php';

const UniqueKey = "ahqQ5TbibZLRGm8tmGCiHymDWqGAiEdh0Rz1JczHWYs4eCrJbPt7Iv2Db4Vlz20GF7oiNCum0hrsAf00iO3lSNw0cBFXdTUi1HueBQGYHEhxe4jHcysFLVH4GRR9pqol";
if(isset($_POST['action']))
{
    try
    {
        switch($_POST['action'])
        {
            case 'getqr':
                {
                    $gumpayHelper = new GumPayApp2AppHelper();
                    $url = $gumpayHelper->GetOrderQR(UniqueKey, $_POST['order'], $_POST['amount'], $_POST['returnUrl'], 60);
                    header('Content-type: application/json');
                    if($url)
                    {
                        echo json_encode(array("result"=> true, "qrimage"=> $url));
                    }
                    else
                    {
                        echo json_encode(array("result"=> false));
                    }
                    break;
                }
            case 'checkpayment':
                {
                    $gumpayHelper = new GumPayApp2AppHelper();
                    $result = $gumpayHelper->CheckOrderComplete(UniqueKey, $_POST['order']);
                    header('Content-type: application/json');
                    if($result)
                    {
                        echo json_encode(array("result"=> true, "transactionId"=> $result));
                    }
                    else
                    {
                        echo json_encode(array("result"=> false));
                    }
                    break;
                }
            case 'getpreauthlink':
                {
                    $gumpayHelper = new GumPayApp2AppHelper();
                    $url = $gumpayHelper->GetPreauthLink($_POST['uniquekey'], $_POST['returnUrl'], 60);
                    header('Content-type: application/json');
                    if($url)
                    {
                        echo json_encode(array("result"=> true, "link"=> $url));
                    }
                    else
                    {
                        echo json_encode(array("result"=> false));
                    }
                    break;
                }
            case 'preauthorizetransaction':
                {
                    $gumpayHelper = new GumPayApp2AppHelper();
                    $token = $gumpayHelper->PreauthorizeTransaction($_POST['uniquekey'], $_POST['userId'], 'HKD', $_POST['amount']);
                    header('Content-type: application/json');
                    if($token)
                    {
                        echo json_encode(array("result"=> true, "token"=> $token));
                    }
                    else
                    {
                        echo json_encode(array("result"=> false));
                    }
                    break;
                }
            case 'capturetransaction':
                {
                    $gumpayHelper = new GumPayApp2AppHelper();
                    $result = $gumpayHelper->CaptureTransaction($_POST['uniquekey'], $_POST['userId'],$_POST['token'], 'HKD', $_POST['amount']);
                    header('Content-type: application/json');
                    if($result)
                    {
                        echo json_encode(array("result"=> true));
                    }
                    else
                    {
                        echo json_encode(array("result"=> false));
                    }
                break;
                }
            case 'canceltransaction':
                {
                    $gumpayHelper = new GumPayApp2AppHelper();
                    $result = $gumpayHelper->CancelTransaction($_POST['uniquekey'], $_POST['userId'],$_POST['token']);
                    header('Content-type: application/json');
                    if($result)
                    {
                        echo json_encode(array("result"=> true));
                    }
                    else
                    {
                        echo json_encode(array("result"=> false));
                    }
                break;
                }
            case 'loadpreauthorizedusers':
                {
                    $gumpayHelper = new GumPayApp2AppHelper();
                    $result = $gumpayHelper->GetPreauthorizedUsers($_POST['uniquekey']);
                    header('Content-type: application/json');
                    if($result)
                    {
                        echo json_encode(array("result"=> true, "users"=>$result));
                    }
                    else
                    {
                        echo json_encode(array("result"=> false));
                    }
                break;
                }
        }
    }
    catch(\Exception $ex)
    {
        header('Content-type: application/json');
        echo json_encode(array("result"=> false, "result_text" => $ex->getMessage()));
    }
}
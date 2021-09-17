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
                    $url = $gumpayHelper->GetOrderQR($_POST['uniquekey'], $_POST['order'], $_POST['amount'], $_POST['returnUrl'], 60);
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
                    $result = $gumpayHelper->CheckOrderComplete($_POST['uniquekey'], $_POST['order']);
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
                    $result = $gumpayHelper->GetPreauthLink($_POST['uniquekey'], $_POST['externalUserId'], $_POST['returnUrl'], 60);
                    header('Content-type: application/json');
                    if($result->Success)
                    {
                        echo json_encode(array("result"=> true, "link"=> $result->Url));
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
                    $result = $gumpayHelper->PreauthorizeTransaction($_POST['uniquekey'], $_POST['externalUserId'], 'HKD', $_POST['amount']);
                    header('Content-type: application/json');
                    if($result->Success)
                    {
                        echo json_encode(array("result"=> true, "token"=> $result->Token));
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
                    $result = $gumpayHelper->CaptureTransaction($_POST['uniquekey'], $_POST['externalUserId'], $_POST['token'], 'HKD', $_POST['amount']);
                    header('Content-type: application/json');
                    if($result->Success)
                    {
                        echo json_encode(array("result"=> $result->Captured));
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
                    $result = $gumpayHelper->CancelTransaction($_POST['uniquekey'], $_POST['externalUserId'], $_POST['token']);
                    header('Content-type: application/json');
                    if($result->Success)
                    {
                        echo json_encode(array("result"=> $result->Cancelled));
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
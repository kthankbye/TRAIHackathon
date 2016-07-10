<?php
//require_once 'functions.php';
include 'functions.php';
require_once 'config.php';
$access_token = ACCESS_TOKEN;
$verify_token = VERIFY_TOKEN;
$hub_verify_token = null;
WebHook();
$input = json_decode(file_get_contents('php://input'), true);
$message = "";
if(isset($input['entry'][0]['messaging'][0]['message']['text']))
{
    $message = $input['entry'][0]['messaging'][0]['message']['text'];
}
//$message = $input['entry'][0]['messaging'][0]['message']['text'];
$sequenceNumber = getSequenceNumber($input);
$message_to_reply = '';
//$payload = $input['entry'][0]['messaging'][0]['message']['text'];

//sendMessage($access_token, $payload, $input);
/*if(checkFirstMessage($sequenceNumber)) {
    $message_to_reply = "Welcome, I am Telecom Regulatory Authority of India BOT.";
}*/
$level = checkLevel($input);
$restartMessage = strtolower($message);
if($restartMessage == "restart") {
    if(restartProcess($input)) {
        $message_to_reply = "You did something wrong, process restarted. Kindly enter your 10 digit mobile number again.";
        sendMessage($access_token, $message_to_reply, $input);
        exit();
    }
}
$helpMessage = strtolower($message);
if($helpMessage == "help") {
    $message_to_reply = "Hello, I am TRAIBot, I will help you to solve your complains and log them. First you have to enter 10 digit mobile number. At any point if something goes wrong, you can easily restart the process by typing 'restart'";
    sendMessage($access_token, $message_to_reply, $input);
    exit();
}
if($level == 0){
    if(!isset($_SESSION['mobileNumber'])) {
        $validateResponse = validateMobile($message);
        $isMobileVerified = false;
        $statusCode = false;
        if($validateResponse === "startError") {
            $messageReplyArray = array('Your number might not start with 9,8 or 7. Kindly Check the number and try again.','Error in number - Number should start with 9,8 or 7', 'You have entered an invalid number, kindly check the number, Start should be with 9 or 8 or 7' );
            shuffle($messageReplyArray);
            sendMessage($access_token, $messageReplyArray[0], $input);
        } else {
            if($validateResponse === "numericError") {
                $messageReplyArray = array('Sir, to assist you better we need your mobile number.', 'To help you properly with your complain, we need your mobile number', 'Kindly enter Mobile Number, it cant be blank.');
                shuffle($messageReplyArray);
                $message_to_reply = $messageReplyArray[0];
                //$message_to_reply = 'Sir, kindly enter the 10 digit mobile number. Only Numbers are accpetable';
                sendMessage($access_token, $message_to_reply, $input);
            }
            else {
                if($validateResponse === "emptyResp") {
                    $messageReplyArray = array('Sir, to assist you better we need your mobile number.', 'To help you properly with your complain, we need your mobile number', 'Kindly enter Mobile Number, it cant be blank.');
                    shuffle($messageReplyArray);
                    $message_to_reply = $messageReplyArray[0];
                    sendMessage($access_token, $message_to_reply, $input);
                } else {
                    if($validateResponse == true) {
                        $statusCode = insertData($input, $_SESSION['mobileNumber']);
                        $isMobileVerified = true;

                    }
                }
            }
        }
        if($isMobileVerified == true) {
            $countryCode = "91";
            $mobileNumber = $_SESSION['mobileNumber'];
            $carrierName = getCarrierLookup($countryCode, $mobileNumber);
            $message_to_reply = "Thanks for entering your mobile number. Your mobile number $mobileNumber belongs to $carrierName TSP.";
            sendMessage($access_token, $message_to_reply, $input);
            if($statusCode == true) {
                //sendMessage($access_token, "Inside Service", $input);
                serviceOptions($input);
                $respUpdate = updateLevel($level + 1, $input);
                //sendMessage($access_token, $payload, $input);
                /*if($respUpdate == true) {
                    redressalMethod($input);
                }*/
                //redressalMethod($input);

                //sendMessage($access_token, $jsonData, $input);
            }
        }
        //$messageReplyArray = array('Sir, to assist you better we need your mobile number.', 'To help you properly with your complain, we need your mobile number', )
        //$message_to_reply = 'Sir, to assist you better '
    }

    /*if(preg_match('[time|current time|now]', strtolower($message))) {
        // Make request to Time API
        ini_set('user_agent','Mozilla/4.0 (compatible; MSIE 6.0)');
        $result = file_get_contents("http://www.timeapi.org/utc/now?format=%25a%20%25b%20%25d%20%25I:%25M:%25S%20%25Y");
        if($result != '') {
            $message_to_reply = $result;
        }
    } else {
        $message_to_reply = 'Hello, ' . getSenderName($input, $access_token);
    }*/
//API Url

//sendMessage($access_token, $message_to_reply, $input);

} else {

    if($level == 1) {
        $payloadList = array('Mobile - Pre Paid', 'Mobile - Post Paid', 'Landline', 'Broadband', 'DTH', 'Cable', 'Wifi');
        $payload = $input['entry'][0]['messaging'][0]['message']['text'];
        if(in_array($payload, $payloadList )) {
            $payload = "You have selected " . $input['entry'][0]['messaging'][0]['message']['text'];
            sendMessage($access_token, $payload, $input);
            $respCode = updateJson($input, $payload, $level);
            if($respCode == true) {
                redressalMethod($input);
                //updateJson($input, $payload)

                updateLevel($level + 1, $input);
                //sendMessage($access_token, $message_to_reply, $input)
            }
        }
   //$respUpdate = updateLevel($level + 1, $input);
    } else {
        if($level == 2) {
            $payloadList = array('Quick Solution', 'Standard Compaint');
            $payload = $input['entry'][0]['messaging'][0]['message']['text'];
            if(in_array($payload, $payloadList)) {
                $payload = "You have selected " . $input['entry'][0]['messaging'][0]['message']['text'];
                sendMessage($access_token, $payload, $input);
                $respCode = updateJson($input, $payload, $level);
                if($respCode == true) {
                    if($input['entry'][0]['messaging'][0]['message']['text'] == "Quick Solution") {
                        commonQuick($input);
                        updateLevel($level + 1, $input);
                    } else {
                        if($input['entry'][0]['messaging'][0]['message']['text'] == "Standard Compaint") {
                            commonStandard($input);
                            updateLevel($level + 1, $input);
                        }
                    }
                    
                    //sendMessage($access_token, $message_to_reply, $input)
                }
            }
        } else {
            if ($level == 3) {
                //$payload = strtolower($input['entry'][0]['messaging'][0]['message']['text']);
                //sendMessage($access_token, $payload, $input);
                if(isset($input['entry'][0]['messaging'][0]['postback']['payload']))
                {
                    $payload = strtolower($input['entry'][0]['messaging'][0]['postback']['payload']);
                }
                else
                {
                    $payload = strtolower($input['entry'][0]['messaging'][0]['message']['text']);
                }
                if (preg_match('[bill|billing issue|bill]', $payload)) {
                    billingSubCategory($input);
                    //sendMessage($access_token, $message_to_reply, $input);
                    
                } else {
                    if(preg_match('[last 3 recharge|recharge|last 3 bills]', $payload))
                    {
                        $billingDataArray = getLastFiveRechargesBill($input);
                        print_r($billingDataArray);
                        for ($i = 0; $i < 3; $i++) {
                            $month = $billingDataArray[$i][0];
                            $amount = $billingDataArray[$i][1];
                            $message_to_reply =  $message_to_reply . "Month: $month, Amount: $amount ||";
                        }

                        sendMessage($access_token, $message_to_reply, $input);
                    }
                    else {

                        if(preg_match('[other|others]', $payload)) {
                            $resp = traiReports($input);
                            //sendMessage($access_token, $resp, $input);
                            //sendReceiptMessage($input);
                            //traiReports($input);
                            $tempData = '**************************';
                            sendMessage($access_token, $tempData, $input);
                            sendMessage($access_token, $resp, $input);
                            sendMessage($access_token, $tempData, $input);
                        } else {


                            if (preg_match('[validate]', $payload)) {
                                echo confirmactualRequest($input,substr($payload,-1));
                                //sendMessage($access_token, $message_to_reply, $input);

                            } else
                                if (preg_match('[vas|value|added|service|value added service]', $payload)) {
                                    vasSubCategory($input);
                                    //sendMessage($access_token, $message_to_reply, $input);

                                }
                                else
                                    if (preg_match('[dataService|data]', $payload)) {
                                        dataSubCategory($input);
                                        //sendMessage($access_token, $message_to_reply, $input);

                                    }else {
                                        if(preg_match('[last 3 requests|last3|last]', $payload))
                                        {
                                            checkLast5Requests($input);
                                        }
                                        //sendMessage($access_token, $payload, $input);
                                    }
                        }
                    }
                    //sendMessage($access_token, $payload, $input);
                }
            }
        }
        //sendMessage($access_token, "fdfdf", $input);
    }
}

?>
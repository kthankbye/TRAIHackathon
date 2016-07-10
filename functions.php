<?php
/**
 * Created by PhpStorm.
 * User: rDx.LoRD
 * Date: 7/9/2016
 * Time: 9:32 PM
 */
session_start();
require_once 'config.php';
include_once 'db_connect.php';
function WebHook()
{
    $hub_verify_token = null;
    $challenge = false;
    if(isset($_REQUEST['hub_challenge'])) {
        $challenge = $_REQUEST['hub_challenge'];
        $hub_verify_token = $_REQUEST['hub_verify_token'];
    }


    if ($hub_verify_token === VERIFY_TOKEN) {
        return $challenge;
    }
    welcomeMessage(ACCESS_TOKEN);
    return false;
}

function getSenderName($input, $accessToken) {
    $senderId = $input['entry'][0]['messaging'][0]['sender']['id'];
    $url = 'https://graph.facebook.com/' . $senderId . '?access_token='.$accessToken;
    $info = json_decode(file_get_contents($url), true);
    $firstName = $info["first_name"];
    $lastName = $info["last_name"];
    $fullName = $info["first_name"] . " " . $info["last_name"];
    return $fullName;
}

function getSequenceNumber($input) {
    if(!isset($input['entry'][0]['messaging'][0]['delivery']['seq'])){
        return -1;
    }
    return $input['entry'][0]['messaging'][0]['delivery']['seq'];
}

function checkFirstMessage($sequenceNumber) {
    if($sequenceNumber == 2) {
        return true;
    }
}

function sendMessage($access_token, $message_to_reply, $input) {
    $sender = $input['entry'][0]['messaging'][0]['sender']['id'];
    $url = 'https://graph.facebook.com/v2.6/me/messages?access_token='.$access_token;
//Initiate cURL.
    $ch = curl_init($url);
//The JSON data.
    $jsonData = '{
    "recipient":{
        "id":"'.$sender.'"
    },
    "message":{
        "text":"'.$message_to_reply.'"
    }
}';
//Encode the array into JSON.
    $jsonDataEncoded = $jsonData;
//Tell cURL that we want to send a POST request.
    curl_setopt($ch, CURLOPT_POST, 1);
//Attach our encoded JSON string to the POST fields.
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDataEncoded);
//Set the content type to application/json
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
//curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
//Execute the request
    //if(!empty($input['entry'][0]['messaging'][0]['message'])){
        $result = curl_exec($ch);
    //}
    //return $result;
}

function getCarrierLookup($countryCode, $mobileNumber) {
    //$data = "cc=$countryCode&phonenum=$mobileNumber";
    //$data = "mobile_number=$mobileNumber";
    //$url = "http://exotel.in/hacks/exotel-geo/iframe-index.php";
    //http://freecarrierlookup.com/getcarrier.php
    $url = "https://catalog.paytm.com/v1/mobile/getopcirclebyrange?number=$mobileNumber";
    $ch1 = curl_init();
    curl_setopt($ch1, CURLOPT_URL,$url);
    curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
    //curl_setopt($ch1, CURLOPT_SSL_VERIFYPEER, false);
    //curl_setopt($ch1, CURLOPT_HTTPHEADER, $header1s);
    /*curl_setopt($ch1, CURLOPT_HTTPPROXYTUNNEL, 0);
    curl_setopt($ch1, CURLOPT_PROXY, '183.111.169.207:3128');*/
    $server_output = curl_exec ($ch1);
    $jsonData = json_decode($server_output, true);
    $carrierName = $jsonData["Operator"] . " and " . $jsonData["Circle"];
    //$carrierName = parseTable($server_output);
    return $carrierName;
}

/*function parseTable($html)
{
    // Find the table
    preg_match("/<table.*?>.*?<\/[\s]*table>/s", $html, $table_html);

    // Get title for each row


    // Iterate each row
    preg_match_all("/<tr.*?>(.*?)<\/[\s]*tr>/s", $table_html[0], $matches);

    $table = array();

    foreach($matches[1] as $row_html)
    {
        preg_match_all("/<td.*?><h2>(.*?)<\/h2><\/[\s]*td>/", $row_html, $td_matches);
        $row = array();
        for($i=0; $i<count($td_matches[1]); $i++)
        {
            $td = strip_tags(html_entity_decode($td_matches[1][$i]));
            $row[$row_headers[$i]] = $td;
        }

        if(count($row) > 0)
            $table[] = $row;
    }
    return $table;
}*/

function serviceOptions($input)
{
    $sender = $input['entry'][0]['messaging'][0]['sender']['id'];
    $jsonData = '{
            "recipient":{
                "id":"'.$sender.'"
            },
            "message":{
                text: "Please select one domain, so we can help you better.",
      metadata: "DEVELOPER_DEFINED_METADATA",
      quick_replies: [
        {
          "content_type":"text",
          "title":"Mobile - Pre Paid",
          "payload":"DEVELOPER_DEFINED_PAYLOAD_FOR_PICKING_PrePaid"
        },
        {
          "content_type":"text",
          "title":"Mobile - Post Paid",
          "payload":"DEVELOPER_DEFINED_PAYLOAD_FOR_PICKING_PostPaid"
        },
        {
          "content_type":"text",
          "title":"Landline",
          "payload":"DEVELOPER_DEFINED_PAYLOAD_FOR_PICKING_Landline"
        },
        {
          "content_type":"text",
          "title":"Broadband",
          "payload":"DEVELOPER_DEFINED_PAYLOAD_FOR_PICKING_Broadband"
        },
        {
          "content_type":"text",
          "title":"DTH",
          "payload":"DEVELOPER_DEFINED_PAYLOAD_FOR_PICKING_DTH"
        },
        {
          "content_type":"text",
          "title":"Cable",
          "payload":"DEVELOPER_DEFINED_PAYLOAD_FOR_PICKING_Cable"
        },
        {
          "content_type":"text",
          "title":"Wifi",
          "payload":"DEVELOPER_DEFINED_PAYLOAD_FOR_PICKING_Wifi"
        }
      ]
            }
        }';
    //return $jsonData;
    replyMessage($input,$jsonData);
}

function replyMessage($input,$jsonData)
{
    $sender = $input['entry'][0]['messaging'][0]['sender']['id'];
    $url = "https://graph.facebook.com/v2.6/me/messages?access_token=" . ACCESS_TOKEN;
    $ch = curl_init($url);

//Encode the array into JSON.
    $jsonDataEncoded = $jsonData;
//Tell cURL that we want to send a POST request.
    curl_setopt($ch, CURLOPT_POST, 1);
//Attach our encoded JSON string to the POST fields.
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDataEncoded);
//Set the content type to application/json
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
//curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/x-www-form-urlencoded'));

//if(!empty($input['entry'][0]['messaging'][0]['message'])){
    $result = curl_exec($ch);
//}
    //return $result;
}

function redressalMethod($input)
{
    $sender = $input['entry'][0]['messaging'][0]['sender']['id'];
    //$jsonArray = array("recipient" => array("id" => $sender), "message" => array("text" => "Cool.. What would you prefer..??", "metadata" => "DEVELOPER_DEFINED_METADATA","quick_replies" => array("content_type"=>"text", "title" =>"Quick Solution", "payload" => "DEVELOPER_DEFINED_PAYLOAD_FOR_PICKING_quick")));
    //$jsonData = json_encode($jsonArray);
    $jsonData = '{
            "recipient":{
                "id":"'.$sender.'"
            },
            "message":{
                text: "Cool.. What would you prefer..??",
                metadata: "DEVELOPER_DEFINED_METADATA",
                quick_replies: [
                {
                  "content_type":"text",
                  "title":"Quick Solution",
                  "payload":"DEVELOPER_DEFINED_PAYLOAD_FOR_PICKING_quick"
                },
                {
                  "content_type":"text",
                  "title":"Standard Compaint",
                  "payload":"DEVELOPER_DEFINED_PAYLOAD_FOR_PICKING_long"
                }
                ]
            }
        }';

    replyMessage($input,$jsonData);
}

function commonQuick($input)
{
    $sender = $input['entry'][0]['messaging'][0]['sender']['id'];
    $websiteLink = websiteLink;
    $jsonData = '{
            "recipient":{
                "id":"'.$sender.'"
            },
            "message":{
                attachment: {
        type: "template",
        payload: {
          template_type: "generic",
          elements: [
          {
            title: "Value Added Services",
            subtitle: "Activated without your consent..??",
            image_url: "'.$websiteLink.'images/VAS.jpg",
            buttons: [{
              type: "postback",
              title: "VAS Disable",
              payload: "VAS",
            }],
          }, {
            title: "Data Services",
            subtitle: "Data Services Activated without Consent",
            image_url: "'.$websiteLink.'images/data.jpg",
            buttons: [{
              type: "postback",
              title: "Data Service Enquiry",
              payload: "dataService",
            }]
          }, {
            title: "Mobile Number Portability",
            subtitle: "MNP status / Info",
            image_url: "'.$websiteLink.'images/portable.jpg",
            buttons: [{
              type: "postback",
              title: "Know MNP status",
              payload: "MNP",
            }]
          }, {
            title: "Account Issue",
            subtitle: "Account not updated..??",
            image_url: "'.$websiteLink.'images/account.png",
            buttons: [{
              type: "postback",
              title: "Account Related Queries",
              payload: "account",
            }]
          }
          ]
        }
      }

            }
        }';
    replyMessage($input,$jsonData);
}

function validateMobile($message) {
    if(!empty($message)) {
        if(preg_match('/^\d{10}$/',$message)) {
            $mobileNumber = trim($message);
            if($mobileNumber[0] == "9" || $mobileNumber[0] == "8" || $mobileNumber[0] == "7") {
                //$_COOKIE['mobileNumber'] = $mobileNumber;
                //setcookie('mobileNumber',$mobileNumber,time() + (86400 * 7));
                $_SESSION['mobileNumber'] = $mobileNumber;
                return true;
            } else {
                return "startError";
            }
        } else {
            return "numericError";
        }
    } else {
        return "emptyResp";
    }
}


function welcomeMessage($accessToken) {
    $url = "https://graph.facebook.com/v2.6/1659287817729694/thread_settings?access_token=$accessToken";
    $data = json_encode(array("setting_type" => "greeting", "greeting" => array("text" => "Welcome, I am Telecom Regulatory Authority of India BOT.")));
    $header1s = array();
    $header1s[] = "Content-Type: application/json";
    $ch1 = curl_init();
    curl_setopt($ch1, CURLOPT_URL,$url);
    curl_setopt($ch1, CURLOPT_POST, 1);
    curl_setopt($ch1, CURLOPT_POSTFIELDS,$data);
    curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
    //curl_setopt($ch1, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch1, CURLOPT_HTTPHEADER, $header1s);
    $server_output = curl_exec ($ch1);
    return $server_output;
}

function checkLevel($input) {
    $senderId = $input['entry'][0]['messaging'][0]['sender']['id'];
    $mysqli = new mysqli(HOST, USER, PASSWORD, DATABASE);
    $stmt = $mysqli->query("SELECT * FROM levels WHERE senderId = '$senderId' ORDER BY updatedTimestamp DESC");
    $rowCount = $stmt->num_rows;
    if($rowCount === 0) {
        return 0;
    } else {
        $row = $stmt->fetch_assoc();
        $latestUpdatedTimestamp = $row["updatedTimestamp"];
        if(timeDifference($latestUpdatedTimestamp) > 30) {
            $resp = restartProcess($input);
            if($resp == true) {
                sendMessage(ACCESS_TOKEN, "Process has been restarted, kindly enter mobile number again.", $input);
            } else {
                sendMessage(ACCESS_TOKEN, "Some error occured.", $input);
            }
        }
        return $row["latestLevel"];
    }
}

function vasSubCategory($input)
{
    $sender = $input['entry'][0]['messaging'][0]['sender']['id'];
    $jsonData = '{
            "recipient":{
                "id":"'.$sender.'"
            },
            "message":{
                text: "Seems like some value added services were activated. Click below to view Last 3 requests activated on your number by your TSP.",
                metadata: "DEVELOPER_DEFINED_METADATA",
                quick_replies: [
                {
                  "content_type":"text",
                  "title":"Last 3 Requests",
                  "payload":"last3"
                }
                ]
            }
        }';

    replyMessage($input,$jsonData);


}
function confirmactualRequest($input,$Id)
{
    $sender = $input['entry'][0]['messaging'][0]['sender']['id'];
    $result = confirmRequest($Id);
    if($result[0][0] == 0)
    {
        sendMessage(ACCESS_TOKEN, "Yes this was not requested by you. a complaint will be lodged for it", $input);
    }
    else
    {
        sendMessage(ACCESS_TOKEN, "It was requested by you.", $input);
    }


}

function checkLast5Requests($input) {
    $sender = $input['entry'][0]['messaging'][0]['sender']['id'];
    $last5 = getLastFiveRequests($sender);
    $jsonData =null;
    $buttons = "";
    foreach ($last5 as $key => $value) {
        $dt = new DateTime("@".$value["2"]);
        if($key != sizeof($last5)-1)
        {
            $buttons .= '{
                    "type":"postback",
            "title":"'.$value["3"].' @'.$dt->format('d-m-Y H:i:s').'",
            "payload":"validate'.$value["0"].'"
          },';
        }
        else
        {
            $buttons .= '{
            "type":"postback",
            "title":"'.$value["3"].' @'.$dt->format('d-m-Y H:i:s').'",
            "payload":"validate'.$value["0"].'"
          }';

        }
    }

    $jsonData = '{
            "recipient":{
                "id":"'.$sender.'"
            },
            "message":{
                "attachment": {
        "type": "template",
        "payload": {
          "template_type": "button",
          "text": "Your Last 3 Requests",
          "buttons":['.$buttons.'
          
          ]
        }
      }
            }
        }';

    replyMessage($input,$jsonData);
    //return $last5;
}

function getLastFiveRequests($senderId) {
    //$senderId = $input['entry'][0]['messaging'][0]['sender']['id'];
    //$senderId = $input['entry'][0]['messaging'][0]['sender']['id'];
    mysql_connect(HOST, USER, PASSWORD);
    mysql_select_db(DATABASE);
    $result = mysql_query("SELECT * FROM responses WHERE senderId = '$senderId' ORDER BY timestamp DESC LIMIT 3") or die(mysql_error());
    $billingData = array();
    $index = 0;
    while($row = mysql_fetch_assoc($result, MYSQL_ASSOC)) {
        $billingData[$index] = array($row['id'], $row['mobile'], $row['timestamp'], $row['requestedType'], $row['endsinDays']);
        $index++;
    }
    return $billingData;
}

function confirmRequest($Id) {
    //$senderId = $input['entry'][0]['messaging'][0]['sender']['id'];
    //$senderId = $input['entry'][0]['messaging'][0]['sender']['id'];
    mysql_connect(HOST, USER, PASSWORD);
    mysql_select_db(DATABASE);
    $result = mysql_query("SELECT requestID FROM responses WHERE id = '$Id' ORDER BY timestamp DESC LIMIT 3") or die(mysql_error());
    $billingData = array();
    $index = 0;
    while($row = mysql_fetch_assoc($result, MYSQL_ASSOC)) {
        $billingData[$index] = array($row['requestID']);
        $index++;
    }
    return $billingData;
}

function timeDifference($lastUpdatedTime) {
    $date = new DateTime();
    $currentTime = $date->getTimestamp();
    $difference = ($currentTime - $lastUpdatedTime) / 60;
    return $difference;
}

function insertData($input, $mobileNumber) {
    $senderId = $input['entry'][0]['messaging'][0]['sender']['id'];
    $mysqli = new mysqli(HOST, USER, PASSWORD, DATABASE);
    $json = json_encode(array("level0" => array("mobile" => $mobileNumber, "level" => 0)));
    $date = new DateTime();
    $time = $date->getTimestamp();
    $stmt = $mysqli->query("INSERT INTO levels VALUES(NULL, '$senderId', '$json', 0, '$time')");
    return true;
}

function updateLevel($level, $input) {
    $senderId = $input['entry'][0]['messaging'][0]['sender']['id'];
    $mysqli = new mysqli(HOST, USER, PASSWORD, DATABASE);
    $stmt = $mysqli->query("UPDATE levels SET latestLevel = $level WHERE senderId = '$senderId'");
    return true;
}

function restartProcess($input) {
    $senderId = $input['entry'][0]['messaging'][0]['sender']['id'];
    $mysqli = new mysqli(HOST, USER, PASSWORD, DATABASE);
    $stmt = $mysqli->query("DELETE FROM levels WHERE senderId = '$senderId'");
    return true;
}

function updateJson($input, $payload, $level) {
    $senderId = $input['entry'][0]['messaging'][0]['sender']['id'];
    $mysqli = new mysqli(HOST, USER, PASSWORD, DATABASE);
    $stmt = $mysqli->query("SELECT json FROM levels WHERE senderId = '$senderId'");
    $row = $stmt->fetch_assoc();
    $jsonData = $row['json'];
    $jsonArray = json_decode($jsonData, true);
    $jsonIndex = "level".$level;
    $jsonArray[$jsonIndex] = array("payload" => "$payload", "level" => $level);
    $jsonEncode = json_encode($jsonArray);
    $result = $mysqli->query("UPDATE levels SET json = '$jsonEncode' WHERE senderId = '$senderId'");
    //$mysqli->close();
    return true;
}

function commonStandard($input)
{
    $sender = $input['entry'][0]['messaging'][0]['sender']['id'];
    $websiteLink = websiteLink;
    $myWebsiteLink = myWebsiteLink;
    $jsonData = '{
        "recipient":{
            "id":"'.$sender.'"
        },
        "message":{
            attachment: {
        type: "template",
        payload: {
          template_type: "generic",
          elements: [
          {
            title: "Others",
            subtitle: "Others Issues",
            image_url: "'.$myWebsiteLink.'images/bill.jpg",
            buttons: [{
              type: "postback",
              title: "Others Issues",
              payload: "Others",
            }],
          },
         
           {
            title: "Billing Issue",
            subtitle: "Billing Consent",
            image_url: "'.$websiteLink.'images/account.png",
            buttons: [{
              type: "postback",
              title: "Bill Issues",
              payload: "BILL",
            }]
          }
          ]
        }
      }
      }
    }';
    replyMessage($input,$jsonData);
}

function billingSubCategory($input)
{
    $sender = $input['entry'][0]['messaging'][0]['sender']['id'];
    $telcomType = checkLevel1Type($input);
    $title = '';
    $prepaidComplain = '';
    if($telcomType == "prepaid") {
        $title = "Last 3 Recharges";
        $prepaidComplain = '{
          "content_type":"text",
          "title":"Wrong charge calls",
          "payload":"DEVELOPER_DEFINED_PAYLOAD_FOR_PICKING_PostPaid"
        },
        {
          "content_type":"text",
          "title":"Tariff related",
          "payload":"DEVELOPER_DEFINED_PAYLOAD_FOR_PICKING_Landline"
        },
        ';
        $postpaidComplains = '{
          "content_type":"text",
          "title":"Bill Issue",
          "payload":"DEVELOPER_DEFINED_PAYLOAD_FOR_PICKING_BillIssue"
        },';
    } else {
        if($telcomType == "other") {
            $title = "Last 3 Bills";
        } else {
            sendMessage(ACCESS_TOKEN, "No record found on your number, Kindly enter different number.", $input);
            restartProcess($input);
        }
    }

    $jsonData = '{
            "recipient":{
                "id":"'.$sender.'"
            },
            "message":{
                text: "Please select one domain, so we can help you better.",
      metadata: "DEVELOPER_DEFINED_METADATA",
      quick_replies: [
        {
          "content_type":"text",
          "title":"'.$title.'",
          "payload":"DEVELOPER_DEFINED_PAYLOAD_FOR_PICKING_LastThree"
        },
        ';
    if($telcomType == "prepaid") {
        $jsonData = $jsonData . $prepaidComplain;
    } else {
        $jsonData = $jsonData . $prepaidComplain;
    }

    $jsonData = $jsonData . '{
          "content_type":"text",
          "title":"MNP related",
          "payload":"DEVELOPER_DEFINED_PAYLOAD_FOR_PICKING_Broadband"
        },
        {
          "content_type":"text",
          "title":"VAS consent",
          "payload":"DEVELOPER_DEFINED_PAYLOAD_FOR_PICKING_DTH"
        },
        {
          "content_type":"text",
          "title":"DS consent",
          "payload":"DEVELOPER_DEFINED_PAYLOAD_FOR_PICKING_Cable"
        }
      ]
            }
        }';
    //return $jsonData;
    replyMessage($input,$jsonData);
}

function checkLevel1Type($input) {
    $senderId = $input['entry'][0]['messaging'][0]['sender']['id'];
    $mysqli = new mysqli(HOST, USER, PASSWORD, DATABASE);
    $stmt = $mysqli->query("SELECT json FROM levels WHERE senderId = '$senderId'");
    $rowCount = $stmt->num_rows;
    if($rowCount === 0) {
        return 0;
    } else {
        $row = $stmt->fetch_assoc();
        $jsonData = $row['json'];
        $jsonArray = json_decode($jsonData, true);
        $payLoadLevel1Type = $jsonArray['level1']['payload'];
        $splitPayload = split('-', $payLoadLevel1Type);
        $payloadType = strtolower(trim($splitPayload[1]));
        //sendMessage(ACCESS_TOKEN, $payloadType, $input);
        if($payloadType == "pre paid") {
            return "prepaid";
        } else {
            return "other";
        }
    }
}

function getLastFiveRechargesBill($input) {
    $senderId = $input['entry'][0]['messaging'][0]['sender']['id'];
    mysql_connect(HOST, USER, PASSWORD);
    mysql_select_db(DATABASE);
    $result = mysql_query("SELECT * FROM billing WHERE senderId = '$senderId' ORDER BY timestamp DESC LIMIT 3") or die(mysql_error());
    $billingData = array();
    $index = 0;
    while($row = mysql_fetch_assoc($result, MYSQL_ASSOC)) {
        $billingData[$index] = array($row['month'], $row['amount']);
        $index++;
    }
    return $billingData;
}

//print_r(getLastFiveRechargesBill("904165816372555"));

function reportsOther($input) {
    $date = new DateTime();
    $time = $date->getTimestamp();
    //sendMessage(ACCESS_TOKEN, "Type your Concern:", $input);
    $senderId = $input['entry'][0]['messaging'][0]['sender']['id'];
    $payload = strtolower($input['entry'][0]['messaging'][0]['message']['text']);
    $mysqli = new mysqli(HOST, USER, PASSWORD, DATABASE);



    $stmt = $mysqli->query("INSERT INTO logs VALUES(NULL, '$senderId', '$payload', '$time')");
    $logId = $mysqli->query("SELECT id FROM logs WHERE senderId = '$senderId' and consent = '$payload'");
    $row = $logId->fetch_assoc();
    $complaintId = $row['id'];
    return "Complaint Id registered :" .$complaintId . " Soon your issue would be solved.";
}


function traiReports($input) {

    $senderId = $input['entry'][0]['messaging'][0]['sender']['id'];
    $mysqli = new mysqli(HOST, USER, PASSWORD, DATABASE);
    $result = $mysqli->query("SELECT json FROM levels WHERE senderId = '$senderId'");
    $rowCount = $result->num_rows;
    if($rowCount === 0) {
        return 0;
    } else {
        $row = $result->fetch_assoc();
        $jsonData = $row['json'];
        $jsonArray = json_decode($jsonData, true);
        $mobileNumber = $jsonArray['level0']['mobile'];
        $msisdn = '';
        $payLoadLevel1Type = $jsonArray['level1']['payload'];
        $splitPayload = split('-', $payLoadLevel1Type);
        $productType = strtolower(trim($splitPayload[1]));
        $srNo = '1-' . mt_rand(20000,30000) . mt_rand(200000,300000);
        $srCreationDate = date('m-d-Y');
        $ticketTypeDescriptionArray = array("Issue Billing nd Charging", "Issues VAS Related");
        shuffle($ticketTypeDescriptionArray);
        $ticketTypeDescription = $ticketTypeDescriptionArray[0];
        $ticketSubTypeArray = array("Charging Validity Problem", "Activation without Consent");
        shuffle($ticketSubTypeArray);
        $ticketSubType = $ticketTypeDescriptionArray[0];
        $ticketSubSubTypeArray = array("Data Charging Dispute", "SchemeOffer Dispute", "Reportd in 24Hrs of activtn");
        shuffle($ticketSubSubTypeArray);
        $ticketSubSubType = $ticketSubSubTypeArray[0];
        $resolvedDate = date('m-d-Y');
        $slaFlag = "SLA MET";
        $reasonCodeArray = array("Benefits nt provided", "Consent ID Not Present");
        shuffle($reasonCodeArray);
        $reasonCode = $reasonCodeArray[0];
        $ticketActionDescriptionArray = array("Benefits credited to customer", "Deactivated nd Waiver Given");
        shuffle($ticketActionDescriptionArray);
        $ticketActionDescription = $ticketActionDescriptionArray[0];
        $resolution = mt_rand(23,109);
        $circle = getCarrierLookup("91", $mobileNumber);
        $resultSQL = $mysqli->query("INSERT INTO crmandbillingcomplaint VALUES ('$circle', '$msisdn', '$productType', '$srNo', '$srCreationDate', '$ticketTypeDescription', '$ticketSubType', '$ticketSubSubType', now(), '$slaFlag', '$reasonCode', '$ticketActionDescription', $resolution)");
        return "Your SR NO. is = " . $srNo;
    }
}
function sendReceiptMessage($input) {
    $senderId = $input['entry'][0]['messaging'][0]['sender']['id'];

}
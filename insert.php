<?php
/**
 * Created by PhpStorm.
 * User: rDx.LoRD
 * Date: 7/10/2016
 * Time: 9:48 AM
 */

//include_once 'config.php';
//$mysqli = new mysqli(HOST, USER, PASSWORD, DATABASE);
//$date = new DateTime();
/*$time = $date->getTimestamp();
$stmmt = $mysqli->query("INSERT INTO billing VALUES(NULL, '904165816372555', '8527326325', 'january', 1543, 1544, '15 January 2016', 'Credit Card - XX89', $time)");*/
//$date = new DateTime();
//sleep(2);
//$time1 = $date->getTimestamp();
//$stmmt = $mysqli->query("INSERT INTO billing VALUES(NULL, '904165816372555', '8527326325', 'febraury', 1543, 1544, '15 February 2016', 'Internet Banking', $time1)");
////$date = new DateTime();
//sleep(1);
//$time = $date->getTimestamp();
//$stmmt = $mysqli->query("INSERT INTO billing VALUES(NULL, '904165816372555', '8527326325', 'march', 1543, 1544, '15 March 2016', 'Paytm Wallet', $time)");
////$date = new DateTime();
//sleep(1);
//$time = $date->getTimestamp();
//$stmmt = $mysqli->query("INSERT INTO billing VALUES(NULL, '904165816372555', '8527326325', 'april', 1543, 1544, '15 April 2016', 'Airtel Money', $time)");
////$date = new DateTime();
//sleep(1);
//$time = $date->getTimestamp();
//$stmmt = $mysqli->query("INSERT INTO billing VALUES(NULL, '904165816372555', '8527326325', 'may', 1543, 1544, '15 May 2016', 'Mobikwik Wallet', $time)");
////$date = new DateTime();
//sleep(1);
//$time = $date->getTimestamp();
//$stmmt = $mysqli->query("INSERT INTO billing VALUES(NULL, '904165816372555', '8527326325', 'june', 1543, 1544, '15 June 2016', 'Credit Card - XX89', $time)");
////$date = new DateTime();
//sleep(1);*/

$date = date_create();
date_timestamp_set($date, 1468124866);
echo date_format($date, 'd-m-Y H:i:s') . "\n";

<?php
require_once '../config/classes/DbConn.php';

session_start();
$_SESSION['output_array'] = array();

$pbz_url = 'https://www.pbz.hr/Downloads/PBZteclist.xml';
$pbz_xml = simplexml_load_file($pbz_url);

$bank = (string)$pbz_xml->ExchRate->Bank;
array_push($_SESSION['output_array'], $bank);

// turns $date_str into date type
$date_str = $pbz_xml->ExchRate->Date;
$date_crt = date_create($date_str);
$date     = date_format($date_crt, 'Y-m-d');
$current_date = date('Y-m-d');

// Initalize DbConn object and connect to database
$db = new DbConn();
$db = $db->connect();

$xml_array = $pbz_xml->ExchRate->Currency;
foreach($xml_array as $node)
{
    $currency = $node->Name;
    $unit     = $node->Unit;
    $buyRate  = str_replace(',', '.', $node->BuyRateForeign);
    $meanRate = str_replace(',', '.', $node->MeanRate);
    $sellRate = str_replace(',', '.', $node->SellRateForeign);

    $output  = 'Exchange rate for ' . $currency . ' currency added to database.<br>';
    $output .= 'Exchange rates for ' . $currency . ' currency on date ' . $date . ':<br>';
    $output .= 'Unit value: ' . $unit . '<br>';
    $output .= 'Buying rate: ' .$buyRate . '<br>';
    $output .= 'Median rate: ' . $meanRate . '<br>';
    $output .= 'Selling rate: ' . $sellRate . '<br><br>';

    $sql = "INSERT INTO pbz_exchange (Currency, Unit, BuyRateForeign, MeanRate, SellRateForeign, created_at)
            VALUES (:Currency, :Unit, :BuyRateForeign, :MeanRate, :SellRateForeign, :created_at)";
    $count_date = "SELECT COUNT(*) FROM pbz_exchange WHERE Currency='$currency' AND created_at='$date'";

    try
    {
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':Currency', $currency);
        $stmt->bindParam(':Unit', $unit);
        $stmt->bindParam(':BuyRateForeign', $buyRate);
        $stmt->bindParam(':MeanRate', $meanRate);
        $stmt->bindParam(':SellRateForeign', $sellRate);
        $stmt->bindParam(':created_at', $date);

        $count = $db->query($count_date);
        $result = $count->fetch();

        if($result[0] > 0)
        {
            $date_exist = 'Exchange rate for ' . $currency .' currency on date ' . $date . ' is already in database.<br>';
            
            array_push($_SESSION['output_array'], $date_exist);
        } else
        {
            $stmt->execute();

            array_push($_SESSION['output_array'], $output);
        }
    } catch(PDOException $e)
    {
        echo 'Error => ' . $e->getMessage();
    }
}
header('Location: ../');
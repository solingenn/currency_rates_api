<?php 
require '../config/classes/DbConn.php';

session_start();
$_SESSION['output_array'] = array();

$hnb_url = 'http://www.hnb.hr/tecajn/htecajn.htm';
$hnb_array = file($hnb_url);

$bank = 'Hrvatska Narodna Banka';
array_push($_SESSION['output_array'], $bank);

// takes date from string in first row
$date_str  = substr($hnb_array[0], 11, 2) . '-';
$date_str .= substr($hnb_array[0], 13, 2) . '-';
$date_str .= substr($hnb_array[0], 15, 4);

// turns string $date_str into date type
$date_crt = date_create($date_str);
$date     = date_format($date_crt, 'Y-m-d');
$current_date = date('Y-m-d');

// Initalize DbConn object and connect to database        
$db = new DbConn();
$db = $db->connect();

foreach($hnb_array as $i => $value)
{
    // skips first row
    if($i < 1){continue;}

    $currency = substr($value, 3, 3);
    $unit     = substr($value, 6, 3);
    $buyRate  = str_replace(',', '.', substr($value, 16, 8));
    $meanRate = str_replace(',', '.', substr($value, 31, 8));
    $sellRate = str_replace(',', '.', substr($value, 46, 8));

    // skips decimals in front of numbers(e.g. 001 => 1)
    if($unit < 10)
    {
        $unit = substr($value, 8, 1); 
    } else if($unit < 100)
    {
        $unit = substr($value, 7, 2);
    }

    $output  = 'Exchange rate for ' . $currency . ' currency added to database.<br>';
    $output .= 'Exchange rates for ' . $currency . ' currency on date ' . $date . ':<br>';
    $output .= 'Unit value: ' . $unit . '<br>';
    $output .= 'Buying rate: ' .$buyRate . '<br>';
    $output .= 'Median rate: ' . $meanRate . '<br>';
    $output .= 'Selling rate: ' . $sellRate . '<br><br>';

    $sql = "INSERT INTO hnb_exchange (Currency, Unit, BuyRateForeign, MeanRate, SellRateForeign, created_at)
            VALUES (:Currency, :Unit, :BuyRateForeign, :MeanRate, :SellRateForeign, :created_at)";
    $count_date = "SELECT COUNT(*) FROM hnb_exchange WHERE Currency='$currency' AND created_at='$date'";

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
<?php 
require '../config/classes/DbConn.php';

if(!empty($_GET['date']))
{
    session_start();
    $_SESSION['output_array'] = array();

    $hnb_url = 'http://hnbex.eu/api/v1/rates/daily/?date=' . urlencode($date);
    $hnb_json = file_get_contents($hnb_url);
    $hnb_array = json_decode($hnb_json, true);

    $bank = 'Hrvatska Narodna Banka (HNBex lista)';
    array_push($_SESSION['output_array'], $bank);

    $request      = date_create($_GET['date']);
    $date         = date_format($request, 'Y-m-d');
    $current_date = date('Y-m-d');

    // Initalize DbConn object and connect to database
    $db = new DbConn();
    $db = $db->connect();

    if($date > $current_date)
    {
        $date_err  = 'Selected date is not valid. Time machine hasn˙t been invented yet!<br>';
        $date_err .= 'Enter one of the past dates or present date.';

        array_push($_SESSION['output_array'], $date_err);

        header('Location: ../');
        exit;
    }

    foreach($hnb_array as $value)
    {
        $currency = $value['currency_code'];
        $unit     = $value['unit_value'];
        $buyRate  = $value['buying_rate'];
        $meanRate = $value['median_rate'];
        $sellRate = $value['selling_rate'];

        $output  = 'Exchange rate for ' . $currency . ' currency added to database.<br>';
        $output .= 'Exchange rates for ' . $currency . ' currency on date ' . $date . ':<br>';
        $output .= 'Unit value: ' . $unit . '<br>';
        $output .= 'Buying rate: ' .$buyRate . '<br>';
        $output .= 'Median rate: ' . $meanRate . '<br>';
        $output .= 'Selling rate: ' . $value['selling_rate'] . '<br><br>';

        $sql = "INSERT INTO hnbex_exchange (Currency, Unit, BuyRateForeign, MeanRate, SellRateForeign, created_at)
                VALUES (:Currency, :Unit, :BuyRateForeign, :MeanRate, :SellRateForeign, :created_at)";
        $count_date = "SELECT COUNT(*) FROM hnbex_exchange WHERE Currency='$currency' AND created_at='$date'";

        try
        {
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':Currency', $currency);
            $stmt->bindParam(':Unit', $unit);
            $stmt->bindParam(':BuyRateForeign', $buyRate);
            $stmt->bindParam(':MeanRate', $meanRate);
            $stmt->bindParam(':SellRateForeign', $value['selling_rate']);
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
}
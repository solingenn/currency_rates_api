<?php

require_once '../vendor/phpoffice/phpexcel/Classes/PHPExcel.php';
require '../config/classes/DbConn.php';

$excel = new PHPExcel();

$sql = "SELECT * FROM hnbex_exchange";
$count_row = "SELECT COUNT(id) FROM hnbex_exchange";

try
{
    $db = new DbConn();

    // Connect to database
    $db = $db->connect();

    // fetch data from database
    $stmt = $db->query($sql);
    $result = $stmt->fetchAll(PDO::FETCH_OBJ);

    // fetch number of rows in table
    $count = $db->query($count_row);
    $rowNo = $count->fetch();

    // selecting active sheet
    $excel->setActiveSheetIndex(0);

    $row = 3;
    foreach($result as $data)
    {
        $sql2 = "SELECT Currency, MeanRate, created_at FROM hnbex_exchange 
                WHERE Currency='$data->Currency' AND created_at='$data->created_at'-INTERVAL 1 DAY";

        // fetching rows day before selected date
        $stmt2 = $db->query($sql2);
        $pastDate = $stmt2->fetch();

        if(empty($pastDate))
        {
            $diff = 'N/A';
        } else
        {
            // calculating MeanRate difference between two dates in %
            $diff = $data->MeanRate - $pastDate["MeanRate"];
            $longDiff = ($diff / $data->MeanRate) * 100;
            $pDiff = substr($longDiff, 0, 5);
        }

        $excel->getActiveSheet()
                ->setCellValue('A'.$row, $data->Currency)
                ->setCellValue('B'.$row, $data->Unit)
                ->setCellValue('C'.$row, $data->MeanRate)
                ->setCellValue('D'.$row, $data->created_at)
                ->setCellValue('E'.$row, ($diff === 'N/A') ? 'N/A' : $pDiff.'%');
        $row++;
    }

    // set column width
    $excel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
    $excel->getActiveSheet()->getColumnDimension('B')->setWidth(9);
    $excel->getActiveSheet()->getColumnDimension('C')->setWidth(14);
    $excel->getActiveSheet()->getColumnDimension('D')->setWidth(12);
    $excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);

    // table headers
    $excel->getActiveSheet()
            ->setCellValue('A1', 'HNBex exchange list') // title
            ->setCellValue('A2', 'Currency')
            ->setCellValue('B2', 'Unit')
            ->setCellValue('C2', 'Median Rate')
            ->setCellValue('D2', 'Date')
            ->setCellValue('E2', 'Difference(%)');
            
    // merging cells for title
    $excel->getActiveSheet()->mergeCells('A1:E1');

    // alignment
    $excel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal('center');
    $excel->getActiveSheet()->getStyle('A2:E2')->getAlignment()->setHorizontal('center');
    $excel->getActiveSheet()->getStyle('A3:E'.$row)->getAlignment()->setHorizontal('center');

    // styling
    # title
    $excel->getActiveSheet()->getStyle('A1:E1')->applyFromArray(
    array
    (
        'font' => array
        (
            'size' => 24, 
            'bold' => true,
        ),

        'borders' => array
        (
            'allborders' => array
            (
                'style' => PHPExcel_Style_Border::BORDER_THIN
            )
        )
    ));

    # headers
    $excel->getActiveSheet()->getStyle('A2:E2')->applyFromArray(
    array
    (
        'font' => array
        (
            'size' => 12,
            'bold' => true
        ),

        'borders' => array
        (
            'allborders' => array
            (
                'style' => PHPExcel_Style_Border::BORDER_THIN
            )
        )
    ));

    // data border
    $excel->getActiveSheet()->getStyle('A3:E'.($row-1))->applyFromArray(
    array
    (
        'borders' => array
        (
            'outline' => array
            (
                'style' => PHPExcel_Style_Border::BORDER_THIN
            ),

             'vertical' => array
            (
                'style' => PHPExcel_Style_Border::BORDER_THIN
            )
        )
    ));

       
    // separating same dates with thick border for better visibility
    // USE THIS CODE IF YOU'RE FETCHING ALL DATES FROM DATABASE
    for($i = 16; $i <= $rowNo[0]; $i += 14)
    {
        $excel->getActiveSheet()->getStyle('A'.$i.':E'.$i)->applyFromArray(array
        (
            'borders' => array
            (
                'bottom' => array
                (
                    'style' => PHPExcel_Style_Border::BORDER_THICK
                )
            )
        ));
    }
   
} catch(PDOException $e)
{
    echo 'Error => ' . $e->getMessage();
}

// redirect to browser (download) instead of saving the result as a file
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="exchange_list.csv');
header('Cache-Control: max-age=0');

// write the result to a file
$file = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
$file->save('php://output');
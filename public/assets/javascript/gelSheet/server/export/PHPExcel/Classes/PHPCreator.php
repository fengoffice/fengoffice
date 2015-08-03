<?php
/**
 * PHPExcel
 *
 * Copyright (C) 2006 - 2008 PHPExcel
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category   PHPExcel
 * @package    PHPExcel
 * @copyright  Copyright (c) 2006 - 2008 PHPExcel (http://www.codeplex.com/PHPExcel)
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt	LGPL
 * @version    1.6.3, 2008-08-25
 */

/** Error reporting */
error_reporting(E_ALL);

/** Include path **/
set_include_path(get_include_path() . PATH_SEPARATOR . '../Classes/');


// Create new PHPExcel object
//echo date('H:i:s') . " Create new PHPExcel object\n";
$objPHPExcel = new PHPExcel();

// Set properties
//echo date('H:i:s') . " Set properties\n";
$objPHPExcel->getProperties()->setCreator("Maarten Balliauw");
$objPHPExcel->getProperties()->setLastModifiedBy("Maarten Balliauw");
$objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
$objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
$objPHPExcel->getProperties()->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.");
$objPHPExcel->getProperties()->setKeywords("office 2007 openxml php");
$objPHPExcel->getProperties()->setCategory("Test result file");


// Add some data
//echo date('H:i:s') . " Add some data\n";
$objPHPExcel->setActiveSheetIndex(0);
$objPHPExcel->getActiveSheet()->setCellValue('A1', '1');
$objPHPExcel->getActiveSheet()->setCellValue('A2', '2');
$objPHPExcel->getActiveSheet()->setCellValue('A3', '3');
$objPHPExcel->getActiveSheet()->setCellValue('A4', '4');
$objPHPExcel->getActiveSheet()->setCellValue('A5', '=SUM(A1:A4)');

//$objPHPExcel->getActiveSheet()->getStyle()->get
//$objPHPExcel->getActiveSheet()->getStyle()->getFont()->setColor(PHPExcel_Style_Color::setRGB(););
$objPHPExcel->getActiveSheet()->getStyle()->getFont()->setItalic();
$objPHPExcel->getActiveSheet()->getStyle()->getFont()->setSize();
$objPHPExcel->getActiveSheet()->getStyle()->getFont()->setUnderline(   PHPExcel_Style_Font::UNDERLINE_SINGLE);

// Rename sheet
//echo date('H:i:s') . " Rename sheet\n";
$objPHPExcel->getActiveSheet()->setTitle('Simple');


// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);

$objPHPExcel->createSheet();


// Save Excel 2007 file
//echo date('H:i:s') . " Write to Excel2007 format\n";
//echo "<br>" . __FILE__ . "<br>";
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save(str_replace('.php', '.xlsx', __FILE__));

$pdf= new PHPExcel_Writer_PDF($objPHPExcel);
$pdf->save("pdfprueba.pdf");


// Echo memory peak usage
//echo date('H:i:s') . " Peak memory usage: " . (memory_get_peak_usage(true) / 1024 / 1024) . " MB\r\n";

// Echo done
//echo date('H:i:s') . " Done writing file.\r\n";

header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Content-Type: application/force-download");
header("Content-Type: application/octet-stream");
header("Content-Type: application/download");
header("Content-Disposition: attachment;filename= prueba.xlsx");
header("Content-Transfer-Encoding: binary ");
readfile("01simple.xlsx");





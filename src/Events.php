<?php

require_once "vendor/autoload.php";
require "vendor/underscore/underscore.php";

$fileName = "termine.xlsx";

$excelReader = PHPExcel_IOFactory::createReaderForFile($fileName);
//$inputFileType = "Excel2007";
//$loadSheets = array("SJ 2015_16 ganz");
// $excelReader->setLoadSheetsOnly($loadSheets);
$excelReader->setLoadAllSheets();
$excelObj = $excelReader->load($fileName);

//get all sheet names from the file
$worksheetNames = $excelObj->getSheetNames($fileName);
$return = array();
foreach($worksheetNames as $key => $sheetName){
//set the current active worksheet by name
$excelObj->setActiveSheetIndexByName($sheetName);
//create an assoc array with the sheet name as key and the sheet contents array as value
$return[$sheetName] = $excelObj->getActiveSheet()->toArray(null, true,true,true);
}
//show the final array
// for($i = 0; $i < 20; $i++) {
// var_dump($return["SJ 2015_16 ganz"][$i]);
// }

$appointments = $return["SJ 2015_16 ganz"];

$_a = __($appointments);
$dates = $_a->pluck("A");

$months = array(
    "Januar",
    "Februar",
    "März",
    "April",
    "Mai",
    "Juni",
    "Juli",
    "August",
    "September",
    "Oktober",
    "Novemeber",
    "Dezember"
);

$startDate = __($dates)->find(function($date) use (&$months) {
    return __::any($months, function($month) use (&$date) {
        return 1 == preg_match("/${month}\s\d+/", $date);
    });
});

$year = null;
preg_match("/\d+/", $startDate, $year);
$year = intval($year[0]);
if ($year < 2000) {
    $year += 2000;
}
var_dump($year);


function applyRegexToString($regex, $string) {
    $matches = null;
    $valid = 1 == preg_match($regex, $string, $matches);

    if ($valid) {
        return $matches;
    }
    return false;
}

function getDateArbitraryString($string) {
    // we have to exclude 00s because time is sometimes in date column
    // dottet date can have year
    $dottedDate = "\d{1,2}\.(?!00)\d{1,2}(\.\d{1,4})?";
    // slashed date must have year
    $slashedDate = "\d{1,2}\/\d{1,2}\/\d{1,4}";

    $day = "(${dottedDate}|${slashedDate})";
    $twoDays = "${day}.+${day}";

    $regex = "/$twoDays|$day/";
    return applyRegexToString($regex, $string);
}




function getFromToDateFromArbitraryString($string) {
    $dateDelimiter = "(\.|\/)";
    $date =  "\d{1,2}${dateDelimiter}\d{1,2}${dateDelimiter}(\d{1,2})?";
    $regex = "/(${date}).*(${date})/";//(-|oder|bis)(${date})/";
    return applyRegexToString($regex, $string);
}

$dateStringsFrom = __::chain($dates)
                  ->map(getDateArbitraryString)
    ->filter(function($value) { return $value != false; })
    ->value();

// $dateStringBoth = __::chain($dates)
//                 ->map(getFromToDateFromArbitraryString)
//                 // ->filter(function($value) { return $value != false; })
//                 ->value();
// var_dump($dates);
var_dump($dateStringsFrom);
//var_dump($dateStringBoth);
    //var_dump($_a->pluck("A"));
<?php
namespace Event;

use PHPExcel;


class Events {
    public static function loadEventsFromFile($filename) {
        $excelReader = \PHPExcel_IOFactory::createReaderForFile($filename);
        //$excelReader->setReadDataOnly();
        $excelReader->setLoadAllSheets();
        $excelObj = $excelReader->load($filename);

        // get all sheet names from the file
        $worksheetNames = $excelObj->getSheetNames($filename);

        // retrieve the first sheet
        $worksheetName = $worksheetNames[0];
        $excelObj->setActiveSheetIndexByName($worksheetName);
        $worksheet = $excelObj->getActiveSheet()->toArray(null, true, true, true);

        return $worksheet;
    }

    public static function exportEventsToJavascript($fromFile, $toFile) {
        $worksheet = self::loadEventsFromFile($fromFile);
        $eventReader = new EventReader();
        $events = $eventReader->readEvents($worksheet);
        EventWriter::writeEventsToFile($events, $toFile);
    }
}

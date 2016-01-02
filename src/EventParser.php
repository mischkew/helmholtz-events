<?php

namespace Event;

use Underscore\Types\Arrays;

class EventParser {
    public static $REGEX = [
        "day" => "/(?!00)\d{1,2}/",
        "dayAndMonth" => "/(?!00)(\d{1,2})(\.|\/)(?!00)(\d{1,2})/",
        // backreference not working
        "dayAndMonthAndYear" => "/(?!00)(\d{1,2})(\.|\/)(?!00)(\d{1,2})(\.|\/)(\d{1,4})/"
    ];

    public static $months = [
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
    ];



    public $multiplePool;
    public $singlePool;

    // Rule abbreviations
    public $D = "(?!00)(\d{1,2})";
    public $Y = "(?!00)(?!0000)(\d{1,4})";
    public $N = "(?:[^\.0-9])";


    public function getDefaultSingleRules() {
        $D = $this->D;
        $Y = $this->Y;
        $N = $this->N;

        return [
            [
                "/${D}\.${D}\.${Y}/",
                function($m) { return [$m[1], $m[0], $m[2]]; }
            ],
            [
                "/${D}\.${D}/",
                function($m) { return [$m[1], $m[0], null]; }
            ],
            [
                "/${D}\/${D}/",
                function($m) { return [$m[1], $m[0], null]; }
            ],
            [
                "/${D}\/${D}\/${Y}/",
                function($m) { return [$m[1], $m[0], $m[2]]; }
            ],
        ];
    }

    public function getDefaultMultipleRules() {
        $D = $this->D;
        $Y = $this->Y;
        $N = $this->N;

        return [
            [
                "/${D}\.${D}\.${Y}${N}+{$D}\.${D}\.${Y}/",
                function($m) { return [[$m[1], $m[0], $m[2]], [$m[4], $m[3], $m[5]]]; }
            ],
            [
                "/${D}\.${D}\.?${N}+${D}\.${D}\.${Y}/",
                function($m) { return [[$m[1], $m[0], null], [$m[3], $m[2], $m[4]]]; }
            ],
            [
                "/${D}\.${D}\.?${N}+${D}\.${D}/",
                function($m) { return [[$m[1], $m[0], null], [$m[3], $m[2], null]]; }
            ],
            [
                "/${D}\.?${N}+{$D}\.${D}\.${Y}/",
                function($m) { return [[$m[2], $m[0], $m[3]], [$m[2], $m[1], $m[3]]]; }
            ],
            [
                "/${D}\.?${N}+${D}\.${D}/",
                function($m) { return [[$m[2], $m[0], null], [$m[2], $m[1], null]]; }
            ]
        ];
    }

    public function __construct() {
        $this->multiplePool = new RulePool();
        $this->singlePool = new RulePool();

        // apply default rules
        $this->singlePool->addRules($this->getDefaultSingleRules());
        $this->multiplePool->addRules($this->getDefaultMultipleRules());
    }

    public function isMultipleDays($dateChunk) {
        return (bool) $this->multiplePool->applyRules($dateChunk);
    }

    public function getSingleDay($dateChunk) {
        return $this->singlePool->applyRules($dateChunk);
    }

    public function isSingleDay($dateChunk) {
        return (bool) $this->singlePool->applyRules($dateChunk);
    }

    public function getMultipleDays($dateChunk) {
        return $this->multiplePool->applyRules($dateChunk);
    }

    public static function isMonth($dateChunk) {
        return Arrays::matchesAny(self::$months, function($month) use (&$dateChunk) {
            return 1 == preg_match("/${month}\s(\d+)/", $dateChunk);
        });
    }

    public static function getYear($dateChunk) {
        $matches = null;
        preg_match("/\d+/", $dateChunk, $matches);
        return $matches[0];
    }

    public static function findFirstMonth($events) {
        return Arrays::from($events)
                    ->pluck("A")
                    ->find(function($date) {
                        return Arrays::matchesAny(self::$months, function($month) use (&$date) {
                            return 1 == preg_match("/${month}\s\d+/", $date);
                        });
                    });
    }

    public static function appendLeadingZero($dateChunk) {
        if (strlen($dateChunk) >= 2) {
            return $dateChunk;
        }

        return "0" . $dateChunk;
    }

    public static function fullYearFrom($year) {
        if ($year < 2000) {
            return $year + 2000;
        }
        return $year;
    }

    public static function testDMY($dateChunk, &$matches = null) {
        return preg_match(self::$REGEX["dayAndMonthAndYear"], $dateChunk, $matches);
    }


    public static function containsDMY($dateChunk) {
        return 1 == self::testDMY($dateChunk);
    }

    public static function testDM($dateChunk, &$matches = null) {
        return preg_match(self::$REGEX["dayAndMonth"], $dateChunk, $matches);
    }

    public static function containsDM($dateChunk) {
        return 1 == self::testDM($dateChunk);
    }

    public static function testD($dateChunk, &$matches = null) {
        return preg_match(self::$REGEX["day"], $dateChunk, $matches);
    }

    public static function containsD($dateChunk) {
        return 1 == self::testD($dateChunk);
    }

    // public static function isSingleDay($dateChunk) {
    //     return self::containsDMY($dateChunk) ||
    //         self:: containsDM($dateChunk);
    // }



    // public static function isMultipleDays($dateChunk) {
    //     // we have to exclude 00s because time is sometimes in date column
    //     //  this date is separated by a single slash indicating options
    //     $dottedDate = "\d{1,2}\.(?!00)\d{1,2}(\.\d{1,4})?";

    //     // slashed date must have year
    //     $slashedDate = "\d{1,2}\/\d{1,2}\/\d{1,4}";

    //     $day = "(${dottedDate}|${slashedDate})";
    //     $twoDays = "${day}.+${day}";

    //     $dayOptional = "\d{1,2}(\.\d{1,2}(\.?|(\.\d{1,4}))?";
    //     $slashedTwoDays = "${dayOptional}\s?\/\s?";

    //     $isSeparatedTwoDays = 1 == preg_match("/$twoDays/", $dateChunk);
    //     $isSlashedTwoDays = 1 == preg_match("//", $dateChunk);

    //     return $isSeparatedTwoDays || $isSlashedTwoDays;
    // }
}
<?php
namespace Event;

use Underscore\Types\Arrays;
use Underscore\Types\Object;


class EventReader {
    public $parser;

    public function __construct() {
        $this->parser = new EventParser();
    }

    /**
     * Chunk off unrelevant lines.
     * Delete empty lines.
     */
    public function cleanEvents($events) {
        // cutoff until first month is found
        $firstMonth = EventParser::findFirstMonth($events);
        $currentLine = $events[0];

        while ($currentLine["A"] != $firstMonth) {
            $currentLine = array_shift($events);
        }
        array_unshift($events, $currentLine);

        // delete empty lines
        return Arrays::from($events)
            ->reject(function($event) {
                return Object::from($event)
                    ->values()
                    ->matches(function($value) {
                        return strlen($value) == 0;
                    })
                    ->obtain();
            })
            ->obtain();
    }

    public static function buildGroup($year) {
        return [
            "year" => $year,
            "lines" => []
        ];
    }

    public function groupEvents($events) {
        $groups = [];
        $year = 0;
        $group = null;

        while (count($events) > 0) {
            $line = array_shift($events);
            $from = $line["A"];

            if (EventParser::isMonth($from)) {
                $year = EventParser::fullYearFrom( (int) EventParser::getYear($from) );
                if ($group) {
                    $groups[] = $group;
                }
                $group = self::buildGroup($year);
            } else if (strlen($from) > 0) {

                if ($this->parser->isMultipleDays($from) || $this->parser->isSingleDay($from)) {

                    if (count($group["lines"]) == 0) {
                        $group["lines"][] = $line;
                    } else {
                        $groups[] = $group;
                        $group = self::buildGroup($year);
                        $group["lines"][] = $line;
                    }

                } else {
                    if ($group && count($group["lines"]) > 0) {
                        $groups[] = $group;
                    }
                    $group = self::buildGroup($year);


                    while (count($events) > 0 &&
                           !$this->parser->isMultipleDays($line["A"]) &&
                           !$this->parser->isSingleDay($line["A"])) {
                        array_shift($events);

                        if (count($events) > 0) {
                            $line = $events[0];
                        }
                    }
                }
            } else if (count($group["lines"]) > 0) {
                $group["lines"][] = $line;
            }
        }

        if ($group && count($group["lines"]) > 0) {
            $groups[] = $group;
        }

        return $groups;
    }
}

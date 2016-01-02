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

    public function readEvents($events) {
        $cleaned = $this->cleanEvents($events);
        $groups = $this->groupEvents($cleaned);

        return Arrays::from($groups)
            ->each($this->groupToEvent)
            ->filter(function($value) { return $value != null; })
            ->obtain();
    }

    public function joinDate($dateChunk, $globalYear) {
        $month = EventParser::appendLeadingZero($dateChunk[0]);
        $day = EventParser::appendLeadingZero($dateChunk[1]);
        $year = EventParser::fullYearFrom($dateChunk[2] ? $dateChunk[2] : $globalYear);

        return "$month/$day/$year";
    }

    public function getToYear($fromChunk, $toChunk, $globalYear) {
        if ((int) $fromChunk[0] > (int) $toChunk[0]) {
            return $globalYear + 1;
        }

        return $globalYear;
    }



    /**
     * Transform a group of lines into a single event object
     */
    public function groupToEvent($eventGroup) {
        $group = $eventGroup["lines"];
        $year = $eventGroup["year"];
        $mainLine = $group[0];

        $event = new Event();
        $eventDate = new EventDate();

        // Check if FROM-cell determines multiple days
        if ($this->parser->isMultipleDays($mainLine["A"])) {
            $dates = $this->parser->getMultipleDays($mainLine["A"]);
            $from = $this->joinDate($dates[0], $year);
            $to = $this->joinDate($dates[1], $this->getToYear($dates[0], $dates[1], $year));
            $eventDate->makeMultipleDays($from, $to);
        }
        // Check if FROM-cell determines one day
        else if ($this->parser->isSingleDay($mainLine["A"])) {
            $dateFrom = $this->parser->getSingleDay($mainLine["A"]);
            $from = $this->joinDate($dateFrom, $year);
            $eventDate->setFrom($from);

            // Check if TO-cell determines one day
            if ($this->parser->isSingleDay($mainLine["B"])) {
                $dateTo = $this->parser->getSingleDay($mainLine["B"]);
                $to = $this->joinDate($dateTo, $this->getToYear($dateFrom, $dateTo, $year));
                $eventDate->setTo($to);
            }
            // this event is happening on one day only
            else {
                $eventDate->makeSingleDay();
            }
        }
        // ignore this group
        else {
            return null;
        }

        $event->setDate($eventDate);

        // Check if TO-cell contains extra information
        foreach ($group as $line) {
            if (strlen($line["B"]) > 0 && !$this->parser->isSingleDay($line["B"])) {
                $event->addExtra($line["B"]);
            }

            $event->addTime($line["C"]);
            $event->addTitle($line["D"]);
        }

        return $event;
    }
}

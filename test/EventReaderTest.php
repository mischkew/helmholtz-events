<?php
namespace Event;

use PHPUnit_Framework_TestCase;


class EventReaderTest extends PHPUnit_Framework_TestCase {

    public $reader;
    public function setUp() {
        $this->reader = new EventReader();
    }

    public function testCleanEvents() {
        $events = [
            ["A" => "Hcuh"],
            ["A" => "f00"],
            ["A" => "bar"],
            ["A" => "März 16"],
            ["A" => "baz"],
            ["A" => "", "B" => "abc"],
            ["A" => "", "B" => ""]
        ];

        $e = $this->reader->cleanEvents($events);
        $this->assertEquals(count($e), 3);
        $this->assertEquals($e[0]["A"], "März 16");
    }

    public function testGroupEvents() {
        $events = [
            ["A" => "August 15"],
            ["A" => "12.1", "B" => "info"],
            ["A" => "12.2", "B" => "info"],
            ["A" => "", "B" => "info2"],
            ["A" => "", "B" => "info3"],
            ["A" => "September 16"],
            ["A" => "12.3", "B" => "info"],
            ["A" => "wrong-date"],
            ["A" => ""],
            ["A" => ""],
            ["A" => "wrong-date"],
            ["A" => "12.3.16", "B" => "info"],
            ["A" => "Oktober 2016"]
        ];

        $groups = $this->reader->groupEvents($events);
        $this->assertEquals(count($groups), 4);

        $this->assertEquals($groups[0]["year"], 2015);
        $this->assertEquals($groups[1]["year"], 2015);
        $this->assertEquals($groups[2]["year"], 2016);
        $this->assertEquals($groups[3]["year"], 2016);

        $this->assertEquals(count($groups[0]["lines"]), 1);
        $this->assertEquals(count($groups[1]["lines"]), 3);
        $this->assertEquals(count($groups[2]["lines"]), 1);
        $this->assertEquals(count($groups[3]["lines"]), 1);
    }

    public function testGroupToEventSingleDay() {
        $group = [
            "year" => 2015,
            "lines" => [
                [
                    "A" => "12.1. (Di)",
                    "B" => "    ",
                    "C" => "18.00 Uhr",
                    "D" => "Informationsabend"
                ]
            ]
        ];

        $event = $this->reader->groupToEvent($group);
        $this->assertTrue($event->date->isSingleDay());
        $this->assertEquals($event->date->getFrom(), "01/12/2015");
        $this->assertEquals(count($event->extras), 0);
        $this->assertEquals(count($event->times), 1);
        $this->assertEquals($event->times[0], "18.00 Uhr");
        $this->assertEquals(count($event->titles), 1);
        $this->assertEquals($event->titles[0], "Informationsabend");
    }

    public function testGroupToEventSingleDayWithExtras() {
        $group = [
            "year" => 2015,
            "lines" => [
                [
                    "A" => "12.1. (Di)",
                    "B" => "9EM, 9A      ",
                    "C" => "   18.00 Uhr",
                    "D" => "Informationsabend   "
                ]
            ]
        ];

        $event = $this->reader->groupToEvent($group);
        $this->assertTrue($event->date->isSingleDay());
        $this->assertEquals($event->date->getFrom(), "01/12/2015");
        $this->assertEquals(count($event->extras), 1);
        $this->assertEquals($event->extras[0], "9EM, 9A");
        $this->assertEquals(count($event->times), 1);
        $this->assertEquals($event->times[0], "18.00 Uhr");
        $this->assertEquals(count($event->titles), 1);
        $this->assertEquals($event->titles[0], "Informationsabend");
    }

     public function testGroupToEventMultipleDays() {
        $group = [
            "year" => 2015,
            "lines" => [
                [
                    "A" => "12.1. (Di)",
                    "B" => "18.1",
                    "C" => "   18.00 Uhr",
                    "D" => "Informationsabend   "
                ]
            ]
        ];

        $event = $this->reader->groupToEvent($group);
        $this->assertFalse($event->date->isSingleDay());
        $this->assertTrue($event->date->isMultipleDays());
        $this->assertEquals($event->date->getFrom(), "01/12/2015");
        $this->assertEquals($event->date->getTo(), "01/18/2015");


        $this->assertEquals(count($event->extras), 0);
        $this->assertEquals(count($event->times), 1);
        $this->assertEquals($event->times[0], "18.00 Uhr");

        $this->assertEquals(count($event->titles), 1);
        $this->assertEquals($event->titles[0], "Informationsabend");
    }

    public function testGroupToEventMultipleDaysInFrom() {
        $group = [
            "year" => 2015,
            "lines" => [
                [
                    "A" => "12.1. (Di) bis 18.1",
                    "B" => "    ",
                    "C" => "   18.00 Uhr",
                    "D" => "Informationsabend   "
                ]
            ]
        ];

        $event = $this->reader->groupToEvent($group);
        $this->assertFalse($event->date->isSingleDay());
        $this->assertTrue($event->date->isMultipleDays());
        $this->assertEquals($event->date->getFrom(), "01/12/2015");
        $this->assertEquals($event->date->getTo(), "01/18/2015");


        $this->assertEquals(count($event->extras), 0);
        $this->assertEquals(count($event->times), 1);
        $this->assertEquals($event->times[0], "18.00 Uhr");

        $this->assertEquals(count($event->titles), 1);
        $this->assertEquals($event->titles[0], "Informationsabend");
    }

    public function testGroupToEventMultipleLines() {
        $group = [
            "year" => 2015,
            "lines" => [
                [
                    "A" => "12.1. (Di)",
                    "B" => "18.1",
                    "C" => "   18.00 Uhr",
                    "D" => "Informationsabend   "
                ],
                [
                    "A" => "",
                    "B" => "",
                    "C" => "12.00 Uhr",
                    "D" => "Wiederholung vom 1.1"
                ]
            ]
        ];

        $event = $this->reader->groupToEvent($group);
        $this->assertFalse($event->date->isSingleDay());
        $this->assertTrue($event->date->isMultipleDays());
        $this->assertEquals($event->date->getFrom(), "01/12/2015");
        $this->assertEquals($event->date->getTo(), "01/18/2015");


        $this->assertEquals(count($event->extras), 0);
        $this->assertEquals(count($event->times), 2);
        $this->assertEquals($event->times[0], "18.00 Uhr");
        $this->assertEquals($event->times[1], "12.00 Uhr");

        $this->assertEquals(count($event->titles), 2);
        $this->assertEquals($event->titles[0], "Informationsabend");
        $this->assertEquals($event->titles[1], "Wiederholung vom 1.1");
    }

    public function testGroupToEventMultipleLinesWithExtras() {
        $group = [
            "year" => 2015,
            "lines" => [
                [
                    "A" => "12.1. (Di) bis 18.1",
                    "B" => "9A, 9EM",
                    "C" => "   18.00 Uhr",
                    "D" => "Informationsabend   "
                ],
                [
                    "A" => "",
                    "B" => "9N",
                    "C" => "12.00 Uhr",
                    "D" => "Wiederholung vom 1.1"
                ]
            ]
        ];

        $event = $this->reader->groupToEvent($group);
        $this->assertFalse($event->date->isSingleDay());
        $this->assertTrue($event->date->isMultipleDays());
        $this->assertEquals($event->date->getFrom(), "01/12/2015");
        $this->assertEquals($event->date->getTo(), "01/18/2015");


        $this->assertEquals(count($event->extras), 2);
        $this->assertEquals($event->extras[0], "9A, 9EM");
        $this->assertEquals($event->extras[1], "9N");

        $this->assertEquals(count($event->times), 2);
        $this->assertEquals($event->times[0], "18.00 Uhr");
        $this->assertEquals($event->times[1], "12.00 Uhr");

        $this->assertEquals(count($event->titles), 2);
        $this->assertEquals($event->titles[0], "Informationsabend");
        $this->assertEquals($event->titles[1], "Wiederholung vom 1.1");
    }

    public function testGetToYear() {
        $this->assertEquals(2015, $this->reader->getToYear(["12"], ["12"], 2015));
        $this->assertEquals(2015, $this->reader->getToYear(["11"], ["12"], 2015));
        $this->assertEquals(2016, $this->reader->getToYear(["12"], ["1"], 2015));
    }

    public function testGroupToEventNewYear() {
        $group = [
            "year" => 2015,
            "lines" => [
                [
                    "A" => "31.12. (Di)",
                    "B" => "  4.1  ",
                    "C" => "18.00 Uhr",
                    "D" => "Informationsabend"
                ]
            ]
        ];

        $event = $this->reader->groupToEvent($group);
        $this->assertTrue($event->date->isMultipleDays());
        $this->assertEquals($event->date->getFrom(), "12/31/2015");
        $this->assertEquals($event->date->getTo(), "01/04/2016");
    }

}
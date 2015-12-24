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
}
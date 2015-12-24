<?php
namespace Event;

use PHPUnit_Framework_TestCase;


class EventDateTest extends PHPUnit_Framework_TestCase
{
    public $singleDay = "03/12/2015";
    public $multiple = [
        "from" => "03/12/2015",
        "to" => "05/12/2015"
    ];
    public $date;

    public function setUp() {
        $this->date = new EventDate();
    }

    public function testFrom() {
        $d = $this->date
           ->setFrom($this->singleDay)
           ->getFrom();

        $this->assertEquals($d, $this->singleDay, "should set and get from-date");
        $this->assertFalse($this->date->isSingleDay(), "should not be a single day");
        $this->assertFalse($this->date->isMultipleDays(), "should not be multiple days");
        $this->assertFalse($this->date->isValid(), "should not be valid");
    }

    public function testTo() {
        $d = $this->date
           ->setTo($this->singleDay)
           ->getTo();

        $this->assertEquals($d, $this->singleDay, "should set and get to-date");
        $this->assertFalse($this->date->isSingleDay(), "should not be a single day");
        $this->assertFalse($this->date->isMultipleDays(), "should not be multiple days");
        $this->assertFalse($this->date->isValid(), "should not be valid");
    }

    public function testSingleDay() {
        $this->date->setFrom($this->singleDay);
        $this->date->makeSingleDay();
        $d = $this->date->getTo();

        $this->assertEquals($d, $this->singleDay, "should set to date");
        $this->assertTrue($this->date->isSingleDay(), "should be a single day");
        $this->assertFalse($this->date->isMultipleDays(), "should not be multiple days");
        $this->assertTrue($this->date->isValid(), "should be valid");
    }

    public function testSingleDayWithFrom() {
        $this->date->makeSingleDay($this->singleDay);
        $d = $this->date->getTo();

        $this->assertEquals($d, $this->singleDay, "should set to date");
        $this->assertTrue($this->date->isSingleDay(), "should be a single day");
        $this->assertFalse($this->date->isMultipleDays(), "should not be multiple days");
        $this->assertTrue($this->date->isValid(), "should be valid");
    }

    public function testMultipleDay() {
        $this->date
            ->setFrom($this->multiple["from"])
            ->setTo($this->multiple["to"]);

        $this->assertFalse($this->date->isSingleDay());
        $this->assertTrue($this->date->isMultipleDays());
        $this->assertTrue($this->date->isValid());
    }

    public function testMakeMultipleDay() {
        $this->date
            ->makeMultipleDays($this->multiple["from"], $this->multiple["to"]);

        $this->assertFalse($this->date->isSingleDay());
        $this->assertTrue($this->date->isMultipleDays());
        $this->assertTrue($this->date->isValid());
    }
}

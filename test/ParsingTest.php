<?php

namespace Event;

use PHPUnit_Framework_TestCase;
use Underscore\Underscore;

error_reporting(E_ALL);

class ParsingTest extends PHPUnit_Framework_TestCase {
    // collection of datestrings and their expected parsing results
    public $dates = [
        ["1.2", ["02", "01", "2016"]]
    ];

    // test cases to detect whether a datestring shows from and to dates or only a single day
    public $stringHasMultipleDates = [
        ["1.2", false],
        ["1.2 oder", false],
        ["Bis 1.2", false],
        ["01.02.2015", false],
        ["1.4.15", false],
        ["3/2/15", false],
        ["03/2/15", false],
        ["16/17.2.15", true],
        ["16./17.2.15", true],
        ["16-17.4", true],
        ["16.-17.4", true],
        ["16.-17.4.15", true],
        ["16.-17.4.2015", true],
        ["16.3 - 17.4", true],
        ["16.3. - 17.3.15", true],
        ["17. oder 18.4", true],
        ["20.3. bis 22.3.", true],
        ["31.12. bis 03.1", true],
        ["15.13.2015 bis 12.12.16", true]
    ];

    public function generalAssert($func, $testTuples) {
        foreach ($testTuples as $t) {
            $this->assertEquals(
                call_user_func($func, $t[0]),
                $t[1],
                "${t[0]} should be asserted as ${t[1]}"
            );
        }
    }

    public function testDMY() {
        $testDMY = [
            ["1", false],
            ["16", false],
            ["1.", false],
            ["13.", false],
            ["14.1", false],
            ["15.3.", false],
            ["15.04.", false],
            ["23.04.15", true],
            ["24.5.2015", true],
            ["14/15/15", true],
            ["13. 13. 13", false],
            ["14. 05", false]
        ];

        $this->generalAssert(
            "Event\\EventParser::containsDMY",
            $testDMY
        );
    }

    public function testDM() {
        $testDM = [
            ["1", false],
            ["16", false],
            ["1.", false],
            ["13.", false],
            ["14.1", true],
            ["15.3.", true],
            ["15.04.", true],
            ["23.04.15", true],
            ["24.5.2015", true],
            ["14/15/15", true],
            ["13. 13. 13", false],
            ["14. 05", false]
        ];

        $this->generalAssert(
            "Event\\EventParser::containsDM",
            $testDM
        );
    }

    public function testD() {
        $testD = [
            ["1", true],
            ["16", true],
            ["1.", true],
            ["13.", true],
            ["14.1", true],
            ["15.3.", true],
            ["15.04.", true],
            ["23.04.15", true],
            ["24.5.2015", true],
            ["14/15/15", true],
            ["13. 13. 13", true],
            ["14. 05", true],
            ["oder", false]
        ];

        $this->generalAssert(
            "Event\\EventParser::containsD",
            $testD
        );
    }

    public function testIsSingleDay() {
        $testSingle = [
            ["1.2", true],
            ["1.2 oder", true],
            ["Bis 1.2", true],
            ["01.02.2015", true],
            ["1.4.15", true],
            ["3/2/15", true],
            ["03/2/15", true],
            ["16/17.2.15", true],
            ["16./17.12.15", true],
            ["16-17.4", true],
            ["16.-17.4", true],
            ["16.-17.4.15", true],
            ["16.-17.4.2015", true],
            ["16.3 - 17.4", true],
            ["16.3. - 17.3.15", true],
            ["17. oder 18.4", true],
            ["20.3. bis 22.3.", true],
            ["31.12. bis 03.1", true]
        ];

        $this->generalAssert(
            "Event\\EventParser::isSingleDay",
            $testSingle
        );
    }

    public function testAppendLeadingZero() {
        $this->assertEquals(EventParser::appendLeadingZero("1"), "01");
        $this->assertEquals(EventParser::appendLeadingZero("0"), "00");
        $this->assertEquals(EventParser::appendLeadingZero("12"), "12");
    }

    public function testFullYearFrom() {
        $this->assertEquals(EventParser::fullYearFrom(15), 2015);
        $this->assertEquals(EventParser::fullYearFrom(0), 2000);
        $this->assertEquals(EventParser::fullYearFrom(2000), 2000);
        $this->assertEquals(EventParser::fullYearFrom(2015), 2015);
    }

    public function testRulePoolMultiple() {
        $p = new EventParser();

        foreach ($this->stringHasMultipleDates as $tuple) {
            $this->assertEquals(
                (bool) $p->multiplePool->applyRules($tuple[0]),
                $tuple[1],
                $tuple[0]
            );
        }
    }

    public function testRulePoolSingle() {
        $p = new EventParser();

        foreach ($this->stringHasMultipleDates as $tuple) {
            $this->assertEquals(
                (bool) $p->singlePool->applyRules($tuple[0]),
                true,
                $tuple[0]
            );
        }
    }


}
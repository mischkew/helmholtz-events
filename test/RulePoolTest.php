<?php
namespace Event;

use PHPUnit_Framework_TestCase;
use Underscore;

class RulePoolTest extends PHPUnit_Framework_TestCase {

    public $rulePool = null;

    function setUp() {
        $this->rulePool = new RulePool();
    }

    function testAddRule() {
        $this->rulePool->addRule("/\d+/", function() { return "abc"; });

        $this->assertEquals(count($this->rulePool->rules), 1);
        $this->assertEquals($this->rulePool->rules[0][0], "/\d+/");
        $this->assertEquals($this->rulePool->rules[0][1](), "abc");
    }

    function testApplyRules() {
        $a = [
            ["/\d{1,2}/", function($matches) { return $matches[0]; }],
            ["/abc/", function($matches) { return $matches[0]; }]
        ];
        $this->rulePool->addRules($a);

        $result = $this->rulePool->applyRules("abc 123");
        $this->assertEquals($result, "12");

        $b = array_reverse($a);
        $this->rulePool->rules = $b;
        $result = $this->rulePool->applyRules("abc 124");
        $this->assertEquals($result, "abc");
    }

}
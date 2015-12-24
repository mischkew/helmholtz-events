<?php
namespace Event;

use Underscore\Types\Arrays;

class RulePool {
    public $rules = [];

    public function addRules($rules) {
        $this->rules = array_merge($this->rules, $rules);
    }

    public function addRule($rule, callable $extractor) {
        $this->rules[] = [$rule, $extractor];
    }

    public function applyRules($dateChunk) {
        return Arrays::from($this->rules)
            ->each(function($tuple) use ($dateChunk) {
                $rule = $tuple[0];
                $extractor = $tuple[1];

                $matches = null;
                $succeeded = 1 == preg_match($rule, $dateChunk, $matches);

                if ($succeeded) {
                    return $extractor($matches);
                }
                return null;
            })
            ->find(function($matches) {
                return $matches != null;
            })
            ->obtain();
    }
}
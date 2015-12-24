<?php
namespace Event;

class EventDate {
    private $from = null;
    private $to = null;
    private $time = null;

    function isMultipleDays() {
        return $this->from != null &&
                            $this->to != null &&
                            $this->from != $this->to;
    }

    function isSingleDay() {
        return $this->from != null &&
                            $this->to != null &&
                            $this->to == $this->from;
    }

    function makeSingleDay($from = null) {
        if (is_null($from)) {
            $from = $this->from;
        } else {
            $this->setFrom($from);
        }
        $this->setTo($from);
    }

    function makeMultipleDays($from, $to) {
        $this->setFrom($from);
        $this->setTo($to);
    }

    function isValid() {
        return $this->from != null &&
                            $this->to != null;
    }

    function hasTime() {
        return $time != null;
    }

    function getFrom() {
        return $this->from;
    }

    function setFrom($from) {
        $this->from = $from;
        return $this;
    }

    function getTo() {
        return $this->to;
    }

    function setTo($to) {
        $this->to = $to;
        return $this;
    }

    function getTime() {
        return $this->time;
    }

    function setTime($time) {
        $this->time = $time;
        return $this;
    }
}

<?php
namespace Event;


class Event {
    /** @var {EventDate} $date **/
    public $date;

    public $extras = [];
    public $titles = [];
    public $times = [];

    public function cleanInput($input) {
        return trim($input);
    }

    public function addExtra($extra) {
        $input = $this->cleanInput($extra);
        if (strlen($input) > 0) {
            $this->extras[] = $input;
        }
        return $this;
    }

    public function addTitle($title) {
        $input = $this->cleanInput($title);
        if (strlen($input) > 0) {
            $this->titles[] = $input;
        }
        return $this;
    }

    public function addTime($time) {
        $input = $this->cleanInput($time);
        if (strlen($input) > 0) {
            $this->times[] = $input;
        }
        return $this;
    }

    public function setDate(EventDate $date) {
        $this->date = $date;
        return $this;
    }
}
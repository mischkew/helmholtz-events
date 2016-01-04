<?php
require __DIR__ . '/../vendor/autoload.php';

$exampleInput = __DIR__ . '/termine.xlsx';
$exampleOutput = __DIR__ . '/event.js';

print "Open file $exampleInput.\n";
\Event\Events::exportEventsToJavascript($exampleInput, $exampleOutput);
print "Written JS code into $exampleOutput.\n";

# helmholtz-events
Reading events from an .xlsx-file for the webpage of Hermann-von-Helmholtz Gymnasium Potsdam (high school).
www.helmholzschule.de

**HEADS UP!**
You are the new or current webmaster at Helmholtzschule? You have found a bug, you want to change the specification or any other help?
Please contact me and see [the development section](#development).

## Convert your .xlsx-files into Javascript. Yay!

<img src="http://i.imgur.com/k5W0p7e.png" width="400">
----->> 
<img src="http://i.imgur.com/Z7MBpNx.png" width="400"> 

## Requirements

You will need 
- PHP >= 5.5
- composer (https://getcomposer.org/)

## Installation

```bash
# clone the repository...
git clone https://github.com/mischkew/helmholtz-events

# and run composer
cd helmholtz-events
composer install
```

## Usage

Have a look at the provided example!

```php
# examples/example.php
<?php
require __DIR__ . '/../vendor/autoload.php';

$exampleInput = __DIR__ . '/termine.xlsx';
$exampleOutput = __DIR__ . '/event.js';

print "Open file $exampleInput.\n";
\Event\Events::exportEventsToJavascript($exampleInput, $exampleOutput);
print "Written JS code into $exampleOutput.\n";
```

## Documentation

**Events::exportEventsToJavascript($fileInput, $fileOutput)**

The only API you will need. Reads an excel file and transforms it into Helmholtzschool-specific javascript code.

## Development

You are a PHP-developer yourself? Feel free to add a pull request after...

- adding an issue and discussing the purpose
- testing your code
- providing an example `termine` file
 
... otherwise just add an issue or file a bug. I am happy to help :)

## License

MIT (c) 2016 - Sven Mischkewitz

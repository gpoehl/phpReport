# phpReport
PHP framework to create reports, data grids or run data driven tasks.

Use **phpReport** with any data source and simply specify the attributes to be cumulated or triggers group changes. 
phpReport calls methods in your class which should execute further tasks or create desired output.

**phpReport** does not read any data nor does it generate a report by itself. The basic idea is that you use the tools you already use to get data from any resource and that you define the output depending on your needs.
Output can be anything like a database table, a file (eg. excel file), an HTML string or an input string to generate a pdf document (e.g. mpdf).  

Requirements
============

**phpReport** has no dependencies. It only requires PHP 7.3.0 or later. 

Support us
==========

Consider supporting development of phpReport with a donation of any value. [Donation button][1] can be found on the
[main page of the documentation][1].

Installation
============

Official installation method is via composer and its packagist package [gpoehl/phpReport](https://packagist.org/packages/gpoehl/phpReport).

```
$ composer require gpoehl/phpReport
```

Usage
=====

The simplest usage of the library would be as follows:

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

// Assuming your data is stored in $data

$rep = new \gpoehl\phpReport($this)
$rep->group('attribute1')
->group('attribute2')   
->sum('attribute3')
->sum(function ($row){return substr ($row, 4,5);})
->setPrototyp(phpReport::all)                        // only for demonstration and testing
->run($data);
echo $rep->output;

```

This will echo a prototyp report having your data grouped by attribute1 and attribute2 while attribute3 and the output of the closure is cumulated.



Setup & Configuration
=====================

All [configuration directives](https://mpdf.github.io/configuration.html) can
be set by the `$config` parameter of the constructor.

Configuration allows naming of methods to be called depending on the rules of your organisation. In case that a directive in the configuration file don't fit your needs in a special use case it can also be passed as a parameter to **phpReport**.


```php
<?php

$rep = new \gpoehl\phpReport($this, ['tempDir' => false]);

```


Online manual
=============

Online manual is available at https://phpReport.github.io/.

For general questions or troubleshooting please use the [phpReport tag](https://stackoverflow.com/questions/tagged/phpReport) at Stack Overflow (and not the project's issue tracker).

Contributing
============

Please read before submitting issues and pull requests the [CONTRIBUTING.md](https://github.com/gpoehl/phpRepor/blob/development/.github/CONTRIBUTING.md) file.

Unit Testing
============

Unit testing for phpReport is done using [PHPUnit](https://phpunit.de/).

To get started, run `composer install` from the command line while in the mPDF root directory
(you'll need [composer installed first](https://getcomposer.org/download/)).

To execute tests, run `vendor/bin/phpunit` from the command line while in the phpReport root directory.

Any assistance writing unit tests for mPDF is greatly appreciated. If you'd like to help, please
note that any PHP file located in the `/tests/` directory will be autoloaded when unit testing.

[1]: https://phpReport.github.io




# phpReport
PHP library to help creating reports, data grids or run data driven tasks.

**phpReport** primarily manages all tasks related to group changes and cumulutes selected values.

When **phpReport** detects a group change related header and footer actions will be triggered. Actions are usually methods or closures. 

Due to the design of **phpReport** you can use it for your most complex tasks. There are no limitations as you have always full control over the program flow.

**phpReport** can run with any data source. So can can use your existing data models with all your business models accessable.
It handles even multi-dimensional arrays or one to many relationsships declared in your models.

**phpReport** does not read any data itself. The best way to get data is using the methods of your own php framwork. But you can use any data access method and pass data row by row or the dataset to **phpReport**. Within one task you can even mix data from any resource like database tables, json files, excel tables and much more.

The same is true for the desired output of your task. You can write data to a database table, a file (eg. excel file), an HTML string, generate a pdf document or send emails. Of course you can do it all together.  

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

A typical usage of the library would be as follows:

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

$rep = (new \gpoehl\phpReport($this))
->group('region', 'regionID')                     
->group('customer', 'customerID')   
->sum('sales', 'mySalesColumn')
->sum('tax', 'myTaxColumn')
->run($data);                                        // $data holds the data set 
echo $rep;

```
public method regionHeader($regionID, $row){
   // your code
}
public method customerHeader($customerID, $row){
   // your code
}
public method customerFooter($customerID, $row){
   // your code
}
public method regionFooter($regionID, $row){
   // your code
}


```



Setup & Configuration
=====================

All [configuration directives](https://phpReport.github.io/configuration.html) have defaults which might be altered in the configuration file.
All directives can also declared as configuration parameter during instantiaton of phpReport.

Configuration allows adopting method names to meet your organisation rules.


```php
<?php
// Example of creating a new report and setting new rule for 'noData' action. 
$rep = new \gpoehl\phpReport($this, [
    // instead of calling the noData action return given string when no data was found
    'actions' => ['noData' => 'No data found.'],      
    ]);

```


Online manual
=============

Online manual is not yet ready. The current version can be viewed at https://gpoehl.github.io/.

For general questions or troubleshooting please use the [phpReport tag](https://stackoverflow.com/questions/tagged/phpReport) at Stack Overflow (and not the project's issue tracker).

Contributing
============

Please read before submitting issues and pull requests the [CONTRIBUTING.md](https://github.com/gpoehl/phpRepor/blob/development/.github/CONTRIBUTING.md) file.

Unit Testing
============

Unit testing for phpReport is done using [PHPUnit](https://phpunit.de/).

To execute tests, run `vendor/bin/phpunit` from the command line while in the phpReport root directory.

Any assistance writing unit tests for phpReport is greatly appreciated. If you'd like to help, please
note that any PHP file located in the `/tests/` directory will be autoloaded when unit testing.

[1]: https://phpReport.github.io




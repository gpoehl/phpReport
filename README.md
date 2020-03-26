# phpReport

phpReport is a modern PHP library designed to be a solid foundation for all
applications working with data sets. 

Ranging from simple reports to complex tasks like writing invoices
 - there are no limits to use this library. 

The open architecture integrates seamless within your environment. 
Your're welcome to use all your data models and components representing your 
business know how as well as any PHP framework or ORM (Object Relation Mapper)
like Popel, Doctrine, Eloquent or Cycle.

Basicly phpReport awaits data and compares values between two consecutive rows.
When they're not equal user defined actions (e.g. group header or footer method)
are executed. This leads to well structured applications which interacts closely with this library.
 
To let you choose the best access strategy for reading data you might feed them to phpReport
all at once, in batches or row by row.




As you can also use any data access tool there are no limitations of data sources.

source you need 
You can even combine (like a join) data from different sources or work with multi dimensional arrays. 


If none of the existing data handler classes fit's your needs you can write your
own dedicated handler or use other tool like phpSpreadsheet to read Excel sheets,
con
and pass data to one of t as aData rows are pTo work with data rows Data rows are 


 

The structural design of aggregating values serves methods like sum, count
(including not null and not zero values), min and max. 
To aggregate values in sheets just combine values with a column key. 

All results are available at any time, for each group level and in any combination
of sheet columns or aggregated fields.

 





Documentation can be viewed at https://phpreport.readthedocs.io/en/latest/

Whenever values of declared groups aren't equal 
assigned actions will be executed. Actions are usually methods to be 
called in the application class(e.g. groupHeader and groupFooter methods). Actions
are the right place to build your output or to do whatever needs to done. 

Other values can be declared to provide aggregate functions like sum, min, max. They
may optionally also aggregated in a sheet. All aggregate funcions as well as a 
lot of different build in counters are available at any time for each group level. 

One of the main advantages of phpReport is the seemless integration into your current
environment which also includes all frameworks. Without any configuration you can
use all your classes, objects and data models. 

Even data retrieval is completely under your control. phpReport accepts any
kind of data and is able to combine data from different sources. Combination means 
that you can join any data sources or work with multi-dimensional arrays. Group changes
and aggregate functions works over all data combinations (we call it dimensions).

Feeding data to phpReport can be separated from the actual application. This means, for example, 
that a controller or a data access class can switch from lazy to eager loading 
or from getting all data at once to reading them in batches to minimize memory. 
The application will not notice this change.  

Requirements
============

The only requirement for **phpReport** is PHP 7.4.0 or later. 

Installation
============

Official installation method is via composer and its packagist package [gpoehl/phpReport](https://packagist.org/packages/gpoehl/phpReport).

```
$ composer require gpoehl/phpReport
```


The same is true for the desired output of your task. You can write data to a database table, a file (eg. excel file), an HTML string, generate a pdf document or send emails. Of course you can do it all together.  


Support us
==========

Consider supporting development of phpReport with a donation of any value. The
sponsor button can be found in the menu bar. 

Usage
=====

The following example shows a basic program structure with two declared groups
and two values to be aggregated.

```php
<?php
    use gpoehl\phpreport\Report;

    require_once(__DIR__ . '/../vendor/autoload.php');
   
    class MyFirstReport {

        // The report object. Use $rep->output to render the output. 
        public $rep;
        
        /**
        * It might also be a good idea to instantiate the Report in your
        * controller.
        */
        public function __construct($data){
            // initialize report
            $this->rep = (new Report($this)) 
            ->data('object')
            ->group('customer')         
            ->group('invoice', 'invoiceID')
            ->aggregate('sales', fn($row) => $row->amount * $row->price);
            // Start execution. $data is an iterable having some data rows
            $this->rep->run($data);
        }

        public function init(){
            return "<h1>My very first report</h1>";
        } 

        public function customerHeader($customer, $row){
            return "<h2>Customer $customer</h2>" . $customer->address;
        } 

        public function invoiceHeader($invoice, $row){
            return "<h3>Invoice ID = $invoice</h3>";
        } 

        // Will be called for each data row
        public function detail($row, $rowKey){
            return "<br>$row->item: $row->description";
        } 

        // $row in footer methods is the previous row, not the one which triggered the group change.
        public function invoiceFooter($invoice, $row){
            return "Total sales for invoice $invoice = " . $this->rep->total->sales->sum();
        } 

        public function customerFooter($customer, $row){
            return "Total sales for customer $customer = " . $this->rep->total->sales->sum();
        }

        public function totalFooter(){
            return 
                "Total sales = $this->rep->total->sales->sum()" .
                "Total number of customers: $this->rep->gc->customer->sum()" .
                "Total number of invoices:  $this->rep->gc->invoice->sum()" .
                "Total number of rows:  $this->rep->rc->sum()" ;
        } 
   }
```

Prototyping
===========
Before developing any action method have a look at the setCallOption() method. Prototype
generates a default report showing a lot of interesting stuff. 


```php
    $this->rep = (new Report($this))
    ->setCallOption(Report::CALL_PROTOTYPE);
```


Setup & Configuration
=====================

phpReport is designed to be very flexible. Check the config.php file and make
desired changes. Adapt action method names to follow your own organisation rules. 


Unit Testing
============

Unit testing for phpReport is done using [PHPUnit](https://phpunit.de/).

To execute tests, run `vendor\bin\phpunit` from the command line while in the phpReport root directory.
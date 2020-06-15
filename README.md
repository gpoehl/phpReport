# phpReport

phpReport is a modern PHP library designed to be a solid foundation for all
applications working with data sets ranging from simple reports to complex tasks
like writing invoices.

The open architecture integrates seamless within your environment.


Data input
----------
Data input is completely decoupled from your application. Use your own data 
access methods, data models and components as well as data access features from
any PHP framework or ORM (Object Relation Mapper).

phpReport accepts data rows being an array, on object or even a string. These
data rows can be passed to phpReport all at once, in batches of any size to  
control memory usage when working with large amount of data or row by row.

Choose the access strategy which seems most suitable for your current application
and change this strategy on demand without touching the application.

Because phpReport accepts any kind of data you're open to read data from any 
source (like scv files, excel sheets, )


Joining data
------------
Joining data in phpReport is used for different pupurses. 

First you can join any data row with any other source. Data sources don't even have
to be the same kind. So you can for example easily combine a row from
a database with rows from an excel sheet.

Another way to join data comes into place when your data row is a data model.
This model usually has methods to get related data (e.g. getOrders in a customer 
model). To automaticly iterate over these orders define this relationship with
the join() method.

To iterate over an multi-dimensional array the join() method is used to declare
which array element holds the next dimension.   

The application itself don't realize that joined data are provided. Groups behave
like in a normal data set and values will also be computed over all data groups.


Working concept
---------------

Basic feature of all report programs is the handling of group changes. 

In phpReport a group change will trigger certain events. Each event ist mapped to
an action like call a method, add a string the output variable, rise a warning, 
throw an error or just do nothing.

Mapping between events and actions is declared in the configuration file but can
be overridden at any time for any real event.    


To let you choose the best access strategy for reading data you might feed them to phpReport
all at once, in batches or row by row.




 

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
            ->group('customer')         
            ->group('invoice', 'invoiceID')
            ->compute('sales', fn($row) => $row->amount * $row->price);
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
# phpReport

phpReport is a modern PHP library solving almost all commonly tasks for applications
working with data groups.
It's designed to help with simple reports and develops full power for difficult
challenges like writing invoices.

The open architecture allows a seamless integration within every environment.

Due to the combination of configuration, declaration and interaction between
phpReport and your application any of your specific operating processes can be
handled without forcing the processes to adapt to the software.

Documentation can be viewed at https://phpreport.readthedocs.io/en/latest/

Configuration
-------------
The purpose of configuration is to map named events to actions. Most likely an action
is a method to be called in the application object but can be anything else.
The config.php file is the place to override system wide default action types and
action method names to meet your naming conventions.

Declaration
-----------
With just four declaration methods (instead of configuration by an array) you'll
setup the skeleton of your application.

The **group()** method declares a new data group and instantiates a group counter.
phpReport will then compare group values between data rows, execute actions
mapped to the group header and footer events and increments the group counter.   

The **compute()** method lets phpReport know that you want use aggregate functions like
sum(), min(), max() or that you'll ask for not null and not zero counters.

The **sheet()** method lets you compute multiple values in a spreadsheet like format.
Sheets can have a fixed number of columns indexed by pre-declared column names or
a variable number of columns based on data values.

With the **join()** method multiple data sources will be combined. Call the other
declaration methods after the join() method when they belong to the joined data.

All aggregate functions and counters are available on each declared group level
over all data sources.

Data input
----------
Data input is completely decoupled from your application. Use your own data
access methods, data models and components as well as data access features from
any PHP framework or ORM (Object Relation Mapper).

phpReport accepts data rows being an array, on object or even a string. These
data rows can be passed to phpReport all at once, row by row or in batches of
any size. This gives greatest flexibility and more control over memory usage when
working with large amount of data.

Choose the access strategy which seems most suitable for your current application
and change this strategy on demand without touching the application.

Between reading and feeding data to phpReport you can modify or filter
the input. So it's easy to work with any data format (like csv files, excel
sheets and json strings).


Joining data
============
Joining data in phpReport can be used for different purposes.

First you can join any data row with any other source. Data sources don't have
to be the same kind. So you can for example easily combine a row from
a database with rows from an excel sheet. Use the same data input methods for
joined data as for the primary data.

Another way to join data comes into place when your data row is a data model.
This model usually has methods or properties providing related data. Declare the
relationship by calling the join() method and phpReport will iterate over these related data.

To iterate over an multi-dimensional array the join() method is used to declare
which array element holds the next dimension.

To the application joining data is largely invisible. Grouping and computing
values behave like data would have been served as a flat record.

Data output
-----------
Usually phpReport doesn't generate any output for you. Actions are the place to define
what has to happen. So you can write data to a database table, a file (eg. excel file),
an HTML string, generate a pdf document or send emails. Of course you can do it all together.  
There's only one rule: Whatever you return from an action will be appended to the
public variable $output.

Usage and prototyping
---------------------
Prototyping is a way to simulate, replace or extend user actions with prototype actions during
development.

Each prototype action generates a html table containing some interesting stuff about
the current data row, data group values and computed values.

The following example for a medium complex application shows the usage of some
basic features.

```php
use gpoehl\phpreport\Report;

class FirstExample{

    public Report $rep;

    /** @var Customer[] Array of customer objects. /*
    public function __construct(array $customers){
        $this->rep  = (new Report($this))
        ->group ('region')
        ->group('customer', 'customerID')
        ->join (['getOrdersByDate'] ,null, null, null, date("Y"))
        ->group ('month', fn($order)=> substr($order->orderDate, 5, 2))
        ->group('orderID')
        ->compute ('discount', false)
        ->join (['getOrderDetails'])
        ->compute ('amount')
        ->setCallOption(Report::CALL_PROTOTYPE);
        $this->rep->run($data);
        echo $this->rep->output;
    }

}
```

The example class above first instantiates a new phpReport object and holds the
reference in a variable. The passed parameter is the object which implements
action methods.

Next two groups named region and customer are declared. The related data will be
taken out of the 'region' and the 'customerID' properties of the customer objects.

To join customer with orders the join method let's declare where to get them from.
In this example we're going to call the getOrdersByDate method and pass an extra
parameter to select only orders of the current year.
 
Note that 'getOrdersByDate' is wrapped in an array to differentiate property and
method names.  

Usually the called method will return related rows and phpReport will iterate over
those rows.

The group('month') call shows how to use a closure to get a value out of a data row.
We also group by orders by orderID.

We also want to compute the discount based on orders per month for a customer.
Assume getting the discount value is very complex and we need or want to do the
calculation ourselves. To do so we can call the compute method with parameter for
the value source equals false. In this case only an calculator object will be
instantiated and linked with the id 'discount' to the total collector.
That's all we need to make sure that our discount will be cumulated by month,
customer, region an to grand total.

The second join instructs to call the getOrderDetail method in the order class
for each order.

The value of the 'amount' property will be computed using a calculator object
linked with the id 'amount' to the total collector.

The setCallOption call activates the prototype function.

To start execution the run() method is called. Here we pass all customer objects
at once to the report object.

Eventually we are echoing the collected return values from the action methods.   

Complex computation of values
===========================

As mentioned above we want / need to calculate the monthly discount ourselves. The
monthFooter method is called after all orders within a month are processed.

The first line shows how to gets the sum of the computed value 'amount' per month.
The second line does a calculation which in reality will be much more complex.
The calculator add method adds the discount value. The cumulated value is
available at all group levels.   

By implementing an action method the related prototype method will (dependent on
the callOption parameter) no longer be called. You can call the prototype
method yourself. No parameter is required as phpReport knows what's to do.

```php
    public function monthFooter($month, $order){
        $amount = $this->rep->total->amount->sum();
        $discount = $amount * (($amount < 1000) ? 0.02 : 0.03);      // That's the complicated formula. :))
        $this->rep->total->discount->add($discount);
        $this->rep->prototype();
    }
```

Data output
===========

The program above is fully working. All you've to do is preparing the desired output.
Implement only those action methods you really need. phpReport is smart enough to
call only existing methods unless you really ask for.

This is also the time to uncomment the two prototype calls made above.  

```php

    public function customerHeader($customerID, $customer){
        return
            $customer->adress' .
            '<br>Dear ' . $customer->name;
        }

    public function customerFooter($customerID, $customer){
        return
           "You placed {$this->rep->gc->orders->sum()} orders".
            . " with an total amount of {$this->rep->total->amount->sum()}." ;
            "Therefore you receive a total discount of {$this->rep->total->discount->sum()}." ;
    }

    // Default method called when no data is found for first data dimension (orders)
     public function noDataDim1($dimID){
            return
                "You haven't placed any order lately. We hope to see you soon again.";
    }

    public function totalFooter(){
        return
            "<h1>Summary page</h1> .
            "<br>Total sales: " . $this->rep->total->sales->sum() .
            "<br>Total number of customers: " . $this->rep->gc->customer->sum() .
            "<br>Total number of orders: " . $this->rep->gc->order->sum() .
            "<br>Total number of rows: " . $this->rep->rc->sum();
    }
```


Requirements
------------

The only requirement for **phpReport** is PHP 8.0 or later.

Installation
------------

Official installation method is via composer and its packagist package [gpoehl/phpReport](https://packagist.org/packages/gpoehl/phpReport).

```
$ composer require gpoehl/phpReport
```


Support us
----------

If this library is valuable for your consider supporting development
of phpReport with a donation.


Unit Testing
------------

Unit testing for phpReport is done using [PHPUnit](https://phpunit.de/).

To execute tests, run `vendor\bin\phpunit` from the command line while in the phpReport root directory.

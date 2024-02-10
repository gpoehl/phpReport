
Introduction
============


|project_name| is a library written in pure PHP that provides a set of classes to 
create any kind of reports.

|project_name| integrates seamless with any framework but can also run alone.

|project_name| manages all tasks when values in declared data fields changes 
between data rows (group changes).

.. code-block:: php

    $rep = (new Report ($this))
    ->group ('customer') 
    ->group ('order', 'orderID')
    ->group ('year', fn($row) => substr($row->orderDate, 0, 4));
    
The example above declares three groups. Group customer is linked to the field
'customer', group order to the field 'orderID' and group year to the first four 
bytes of the orderDate.

To cumulate values call the compute function like

.. code-block:: php




    ->cumulate (substr($row->orderDate, 0, 4'orderValue') 
    ->cumulate ('oderAmount')









To cumulate values like in a spreadsheet call the sheet function like

.. code-block:: php

    ->sheet ('orderValue') 
    ->cumulate ('oderAmount')

    ->group ('year', fn($row) => substr($row->orderDate, 0, 4));
 

Cumulating values is as easy as  

Values of other data fields can be cumulated to get totals or subtotals at any
time for all declared groups.

to group changes and calculates
 
values to provide totals and subtotals at any time for all declared groups.

There is no need to create any 'printed output'. |project_name| can be used
for all kind of jobs where actions need to be performed based on different 
values in declared fields between two data rows.

grouped or tasks  most typical scenario but there are a lot of other use cases. 

Creating reports it the most typical scenario but there are a lot of other use cases. 


|project_name| is a solid foundation for most applications working with
data sets having to deal with group changes or to calculate totals and subtotals.
Creating reports it the most typical scenario but there are a lot of other use cases. 

While |project_name| will handle most of the repetive tasks it is wide open to 
let you use all your business objects and methods. It also integrates seamless
without any configuration into any framework. 

The absence of data input tools permits the use of your own tools. This has the
advantage of 

* Nothing extra to learn
* Free selection of the most suitable access strategie
* Access to all data sources
* Working with all kind of data like arrays, csv files, Excel tables or data objects

To enhance the data input capabilities you can easily combine data rows with any other
data sources and handle them together as a single source. 
This is implemented by the `join` function and let's you

* Iterate over nested or multidimensional arrays
* Iterate over one to many relations provided by data objects
* Read and iterate over additional data from any source

|project_name| also don't prepares any output. Actions are invoked on certain
events (e.g. call a method `customerHeader` after a group change) which gives 
you 100% control over what and how something will be done. Create most complex
reports, create grid tables, print charts, write data into a file or database, 
send mails, create pdf files or do whatever else comes into your mind.

Of course there is much more |project_name| has to offer. 

Aggregating values    
  By calling the `compute` method a calculator object will be instantiated. This
  will aggregate the declared source value and provides for all declared
  groups totals, subtotals and running totals as well as counters for values 
  being not null or empty. The calculator object might also determine minimum 
  and maximum values.

Sheets
  Sheets aggregates values vertically. This makes tabular representation of data
  a snap. Columns can be pre-defined or will be instantiated on demand based on the
  value of a declared key source. 

  Grouping, ranging and filtering of sheet columns provides countless combinations
  for building totals.

Prototyping
  Prototyping is a way to simulate, replace or extend user actions with prototype actions during
  development.

  Each prototype action generates a html table containing some interesting stuff about
  the current data row, data group values and computed values.
 

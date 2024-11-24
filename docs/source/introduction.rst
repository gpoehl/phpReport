
Introduction
============


|project_name| is a pure PHP library to create any kind of reports or other 
applications working with data groups.

|project_name| can be used within any framework without additional configruation.

|project_name| manages all tasks when values in declared data fields changes 
between data rows (group changes).

.. code-block:: php

    $rep = (new Report ($this))
    ->group ('customer') 
    ->group ('order', 'orderID')
    ->group ('year', fn($row) => substr($row->orderDate, 0, 4));
    
The example above declares three groups. Data for the customer group is taken
from the field 'customer', for the order group from the 'orderID' field and 
the year group from the first four bytes of the orderDate.

Values can be cumulated by calling the compute() method.

.. code-block:: php

    ->compute ('oderAmount')
    ->compute ('orderDate', fn($row) => substr($row->orderDate, 0, 4)) 

The rules how data will be get from the data row are the same as for the group() method.

To cumulate values like in a spreadsheet call the sheet function. It's very similar like
the compute function but requires an additional field to be used as a key. 

.. code-block:: php

    ->group ('year', fn($row) => substr($row->orderDate, 0, 4));
    ->sheet ('orderValue', fn($row) => substr($row->orderDate, 4, 2), 'oderAmount')
   
The snippet above declares a group for the year of the orderDate and cumulates
the 'orderAmount' in an collector indexed by the month of the orderDate.    
 
Cumulated values provides totals and subtotals at any time for all declared groups.


Creating reports it the most typical scenario when using |project_name| but there
are a lot of other use cases. |project_name| can be used for all kind of jobs 
where actions need to be performed on grouped data.  
That's why there is no need to create any 'printed output'. 


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
 

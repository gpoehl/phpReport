Data input
==========

Data input is completely decoupled from your application. Use your own data 
access methods, data models and components as well as data access features from
any PHP framework or ORM (Object Relation Mapper).

phpReport accepts data rows being an array, on object or even a string. These
data rows can be passed to phpReport all at once, row by row or in batches of 
any size. This gives greatest flexibility and more control over memory usage when
working with large amount of data.

It also allows complete seperation between a controller class and the application.
The controller might feed the report object with the required data. 

Choose the access strategy which seems most suitable for your current application
and change this strategy on demand without touching the application.

Between reading and feeding data to phpReport you can modify or filter
the input. So it's easy to work with any data format (like csv files, excel 
sheets and json strings).
 

|project_name| accepts data in three different ways.

    * Passing all data within an iterable to the run() method.
        |project_name| iterates over the data set and calls the next() method 
        for each entry.
        
    * Passing chunks of data within an iterable by calling the run()
        method for each chunk while setting the parameter *finalize to false* or
        by calling the nextSet method.
        |project_name| iterates over the data set and calls the next() method 
        for each entry. To finalize the job either call the end() method after
        the last chunk or set the finalize parameter for the last chunk to true. 
   
    * Passing single rows by calling the next() method for each data row. This
        is in many cases the most efficent way as you don't have to collect your
        data into an array. To finalize the job just call the end() method after
        processing the last row.

Joining Data
------------

Joining data in phpReport can be used for different pupurses. 

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
 

.. php:method:: join ($name, $source = null, $actions = null, ...$params): Report
 
    :param string $name: The name to be used for this data set. 
     This name might be used to build method names related to a data set and must
     be unique between all data sets.
     Data rows can retrieved by this name or by an id. The id for the first data set
     is always 0. So each call of the join methods incremets this id.

    :param mixed $source: The source for the joined data. Defaults to the name.
     When source is a callable return the whole the data set or false and call the 
     nextSet() method or the next() method for each data row. 
 
    :param iterable|null $actions: Array of data set related actions to replace configurated actions.

    :param mixed $params: Variadic parameters to be passed to $source.

    :returns: $this which allows method call chaining.

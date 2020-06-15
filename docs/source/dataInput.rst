Data input
==========

|project_name| doesn't read data itself. What seems to be a lack of functionality
on the first glance is in reality a huge benefit.

You can use the same tools for data retrieval you are alreay familar with.
You can even use any ORM tools or your own data models without any modification.

So when running applications with |project_name| you have
full control over authorisation, authentification, caching and much more. 

Using your own methods for data retrieval allows you to access data from any
source (any database and any file type like csc, xml, json, excel sheets, etc.) 
and also to use data from different sources within the same job.

Out of the box data rows can be arrays or objects. To work with other sources
just convert them while reading or create your onw data handler.

All other parametes belongs to multi dimensional data.
 

|project_name| accepts data in three different ways.

    * Passing all data within an iterable to the run() method.
        |project_name| iterates over the data set and calls the next() method 
        for each entry.
        
    * Passing chunks of data within an iterable by calling the run()
        method for each chunk while setting the parameter *finalize to false*.
        |project_name| iterates over the data set and calls the next() method 
        for each entry. To finalize the job either call the end() method after
        the last chunk or set the finalize parameter for the last chunk to true. 
   
    * Passing single rows by calling the next() method for each data row. This
        is in many cases the most efficent way as you don't have to collect your
        data into an array. To finalize the job just call the end() method after
        processing the last row.

Multi-Dimensional Data
----------------------

Handling of multi-dimensional data makes |project_name| extreme powerful.
It let's you iterate over nested arrays with the same functionality as for the
primary data set. So you can define groups and aggregate values for any data
dimension.

Data dimension can exist in form of nested arrays, object methods delivering
related data or even completely independent.

Instead of joining data during data retrieval you can get related data at the
moment you need them. This can save a lot of memory consumpiton.
 


.. php:method:: data($source, $noData, $rowDetail, $noGroupChange, ...$params): Report

    Called when group values between two rows are not equal. Each group has
    its own groupHeader. 

    Group headers are called from the changed group level down to the lowest
    declared group (within an data dimension).

    :param mixed $source: The source for the next data dimension. When source is
     a callable just return the whole the data set or return false and call the 
     nextSet() method or the next() method for each data row.  
    :param mixed $noData: The action to be executed when $source doesn't return any data.
    :param mixed $rowDetail: The action to be executed for each row returned by $source.
    :param mixed $noGroupChange: The action to be executed when two consecutive rows don't trigger
     a group change.
    :param mixed $params: Variadic parameters to be passed to $source.
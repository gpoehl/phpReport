Collector
=========

A collector class is designed to hold and manage multiple items. An item can 
be an other collector or an calculator object.

.. include:: defaultCollectors.rst


The main responsibilty of an collector is to call methods in assigned items.
To make sure that the cumputation of calculated values works correct calculator
objects must be registered to the **total** collector or to one of his child collectors. 

The collector class has the ArrayAccess interface and the magic __get method
implemented which allows a broad range of access options.

The visibility of the $items array is public. This allows maximum speed when accessing
an item and gives the opportunity to apply all php array methods. 


Add items
---------

Items will be added (or registered) to an collector usually by calling the calculate()
or sheet() methods of the **phpReport** class.

You may can also add items by the addItem() method. This is especially desired when
you want to group multiple item objects. 

Adding items to a sheet collector is hidden and will be 
internally handled based on key values of the add() method. 


Alternate item keys
-------------------
Items are stored in the $item array indexed by the name given when calling the
addItem() method. You can also apply an alternate key to allow accessing an item
by the alternate key as well. 

The setAltKeys() method will set many alternate keys while the setAltKey()
method one alternate key.

The group conter collecor uses alternate keys to allow accessing the counters
by the group level and by the group name.


Get items
---------

To access an item you can use the getItems() method for all items or the getItem()
method to get one item. 

The magic __get() and the array access get methods calls the getItem() method.
The item will returned when it exist either by the item array key or by the alternate
key.

.. code-block:: php

    $this->rep = new phpReport($this);
    $this->rep->compute('sales');
    // All of the following statements will return the same item.
    $item = $this->report->total->getItem('sales');
    $item = $this->report->total->items['sales']);
    $item = $this->report->total->sales;
    $item = $this->report->total['sales'];
   

Aggregate methods
-----------------

All aggregate methods implemented in the calculator classes have their counterpart
it the collector classes.

The collector aggregate methods calls the same methods for each assigned item.
Returned results will be returned either as an array indexed by the item key or
as an scalar aggregated value.


Subset of items
---------------

To apply aggregate methods only on some items you can build a subset by calling the
following filter methods. Each of them will return a cloned collector object with
just the filtered items.

To use the cloned collector multiple times hold the reference to the cloned collector in
a variable. 

.. php:method:: range(...$ranges): AbstractCollecotor

    Extract ranges of items.
   
    Returns ranges of items located between start and end keys.
     
    When a range is an array value1 is the start and value2 the end key.
    When one of the keys don't exist the value of the altKey will be used instead.
    When the items still doesn't exist an error will be thrown.
     
    If start key equals Null the range begins at the first item.
    When the end key equals Null the range ends at the last item.
       
    When a range is not an array then the item with the corresponding key or
    altKey is returned if it exist. If this doesn't exist php raise a notice.
     
    Item keys are preserved. Sort order within ranges are preserved. Ranges
    are returned in given order. When items belong to multiple ranges only
    the first occurence will returned.
 
    :param array|int|string[] $ranges Ranges or item keys for items to be filtered.
    :return AbstractCollector Cloned collector with items in ranges.
    :throws InvalidArgumentException When start or end item doesn't exist.

.. php:method:: between(...$ranges): AbstractCollecotor

    Filters items where key is between values.
     
    Iterates over each collector item. If a range is an array and the item key
    is between value1 and value2 of this range (inclusive) the item is returned.
     
    If the range isn't an the item with the corresponding key is returned. 
     
    If a range matches the key of a named range then the named range value will
    be used to filter the items.
     
    Item keys and sort order are preserved.
 
    :param array|int|string[]: $ranges Ranges or item keys for items to be filtered.
    :returns: AbstractCollector Cloned collector with items in ranges.

.. php:method:: filter(callable $callable): 

    Filters items using a callback function.
    Iterates over each item in the array passing key and value to the callback
    function. If the callback function returns TRUE, the current item is returned
    into the cloned collector. Item keys are preserved.
 
    :param callable $callable: The callback function to use. 
    :returns: AbstractCollector Clone of current collector with filtered items

.. php:method:: cmd(callable $command, ...$params): AbstractCollector

    Alter item collection by executing a php array command.
 
    :param callable $command: Any php array command which accepts an 
      array as the first parameter. 
    :param mixed[] $params: Additional parameters passed to the php command.
    :returns: AbstractCollector Clone of current collector with applied command
      on the items array. 

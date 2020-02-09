Sheet
-----

Calculate values in an tabular form like in a spreadsheet.

Sometimes a sheet can eliminate the need for declaring a group or a data dimension.
You can also calculate the same value in different sheets so that results are
grouped by different keys.


The sheet() method instantiates a Sheet or a FixedSheet object. Both of them are
special variants of the collector object and will be assigned to the total 
collector ($total). 

For each column in a sheet a calculator object will be instatiated and linked 
via the $items array property. 
 
.. note::
   All calculator objects within a Sheet or FixedSheet object are of the
   same type.
    
Group levels are like sheet rows and data keys like sheet colums.



 


.. php:method:: sheet(string $name, $value, $headerAction = null, $footerAction = null)

    Calculate attributes in a sheet.

    Sheet is a collection of calculators for a horizontal representation of a value.
    Call this method once for each sheet. 

    :param string $name: Unique name to reference the sheet object. The
      reference will be hold in $this->total.

    :param mixed $value: Source of the key and value to be calculated. 
      Must be served in an array with only one entry were key is the array key and value
      the value. 
      Key and attribute name are attribute names when data row is an object or
      or when row is an array the element keys.
      It's also possiblbe to use a closure which returns an array [key => value].
      False to just instantiate and reference the sheet. To execute the calculation
      call the add() method of the sheet object. This is very useful when 
      getting the key or value to be calculated is complicated and / or you need
      these data on the detail level. 

      .. tip::
        Using the array_column function might declaring the latest data dimension
        redundant.

    :param int|null $typ: The calculator type. 
       Typ is used to choose between a calculator class. Options are XS, REGULAR
       and XL. Defaults to XS. Typ belongs to all sheet items.

    :param mixed $fromKey: To use a fixed sheet declare the first 
      calculator name. Pass an array when sheet names are not in an sequence.
      Example: ['young', 'mid-aged', 'old'l
      Null for sheets where calculators are instantiated for each key value.

    :param mixed $toKey: The last calculator name for fixed sheet. FromKey 
      will be icremented until $toKey is reached.

    :param int|null $maxLevel: The group level at which the value will be 
      added. Defaults to the maximum level of the dimension. Might be less when
      calculated data are only needed on higher levels.

    :returns: $this which allows method call chaining.



FixedSheet class
................

FixedSheet classes are best used when you know which columns you need and want
them to be instantiated all at once. To do so pass an array of column names or
the starting and ending name to the sheet() method.
When starting name is a string make sure that the ending name can be reached
by incrementing the starting name.

.. code-block:: php

    $rep = new Report ($this);
    // Declare season names as columns 
    $rep->sheet ('sales', ['regionID' => 'amount'], ['spring', 'summer' ,'autumn', 'winter'])
    
    // Declare columns 1 to 12 (e.g. to represent month) 
    $rep->sheet ('sales', ['regionID' => 'amount'], 1, 12)

    // Declare columns a, b, c, d, e and f 
    $rep->sheet ('sales', ['regionID' => 'amount'], 'a', 'f')


When you try to add a value to an not existing column an exception will be thrown.

Sheet class
...........

Sheet classes don't have a fixed number of columns. Columns will be instantiated 
whenever a data row delivers a new column name.  

If you need columns in a sorted order you should sort your data accordingly.
If this in not suitable ksort the $items property of the sheet object.

.. code-block:: php

    $rep = (new Report ($this))
    ->sheet ('sales', ['regionID'=> 'amount'])

Calculator
----------

phpReport comes with 3 different calculator classes. The reason behind is that
only nessessarty operations will be performed.

In most cases you don't need the minimum or maximum value of an attribute. 
Identifying those values is time consuming. The same is true for the number
of rows having a not null or a not zero value.

The classes provided are:
 * CalculatorXS (default)
 * Calculator
 * CalculatorXL 

The CalculatorXS class has the minimum functionality. It's perfect to cumulate
any value or to increment any counter. To increment a value (or better a counter)
just call the inc() method. It's the same as calling add(1) method. 

The Calculator class don't have the incrememt method but counts the not null
and not zero values.

The CalculatorXL class extends the Calculator class and detects the minimum
and maximum values of an attribute. Use the min() and max() methods to get
these values.


To cumulate any value you can use the sum() or sheet method.

The sum method cumulates a single value while the sheet method cumulates the value depending on an key.

Example

.. code-block:: php

    $rep = (new Report ($this))
    ->calculate ('amount')
    ->calculate ('price', function($row, $rowKey) {return $row->amount * $row->pricePerUnit;})



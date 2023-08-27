Calculator
----------

phpReport comes with 3 different calculator classes for calculation with numeric 
values and 2 classes for calculation using bcmath functions. 

Each set of those calculators offer different functionality while classes which
names end with 'XS' offers basic functions. Those which names ends with 'XL' offers
the maximum functions and without and suffix the medium set of functions.

The classes provided are:
 * CalculatorXS (default)
 * Calculator
 * CalculatorXL

or with bcmath
 * CalculatorBcmXS 
 * CalculatorBcm
 * CalculatorBcmXL

The CalculatorXS class has the minimum functionality. It's perfect to cumulate
any value or to increment any counter. To increment a value the inc() method
does the same as calling 'add(1)' method.

The Calculator class don't have the incrememt method but counts the not null
and not zero values.

The CalculatorXL class extends the Calculator class and detects the minimum
and maximum values of an attribute. Use the min() and max() methods to get
these values.

Note:
The bcm classes are working wiht the scale parameter. Scale works exactly like 
in the original bcmath functions. So digits after the scale parameter will be 
truncated (same as the floor function). 


The compute() method instantiates a calculator object which provides aggregate functions.
The sheet() method instantiates a sheet colletor which holds many calculator objects.


Example

.. code-block:: php

    $rep = (new Report ($this))
    ->data('object')
    ->compute ('amount')
    ->compute ('price', fn($row, $rowKey) => $row->amount * $row->pricePerUnit);
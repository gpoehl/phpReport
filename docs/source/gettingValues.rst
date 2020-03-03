Getting values
==============

Almost all methods which initializes an |project_name| applicaton have a parameter
called value. Of course you shouldn't pass a real value but the information
where to find the value.

The following table shows all options and the differences between objects and arrays.

    .. list-table:: Value sources
        :widths: auto
        :header-rows: 1

        * - value
          - row is an array
          - row is an object
        * - string or integer
          - value of $row[$value]
          - value of $row->$value
        * - closure
          - Returned value of closure
          - Returned value of closure.
        * - Array with one element    
          - Returned value of $value method in target class
          - | Returned value of $value function in row object.
            | Only $params will be passed to the function. 
        * - Array with two elements    
          - | Returned value of callable.
            | When first element is a classname it is a static call.
          - Same as for arrays:

When the value source is a closure or a callable the variadic parameters will
also passed to the method. 
        





     
   
   






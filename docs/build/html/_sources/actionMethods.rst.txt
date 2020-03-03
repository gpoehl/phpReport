Action methods
..............

The deault for most event actions is calling a method in the target class.
The following description of methods are referring to the default method names.
 
.. note:: The method names below are the method keys. Method keys are mapped
          to real method names by several options and rules.

Data independent methods
------------------------ 

These methods are called even when no data are provided.  

.. php:method:: init()

    First called method which allows you to initialize application properties independent
    from the __construct method.  

.. php:method:: close()
        
    Last called method to clean up the dishes independent from __destruct method.

.. php:method:: totalHeader()

    Called once to build the total header page of the report.

.. php:method:: totalFooter()

    Called once to build the total footer page of the report.

Data driven methods
-------------------    

.. php:method:: noData()

    This method is only called when the given data set is empty. 
    In this case the following methods will never be called.

.. php:method:: groupHeader($groupValue, $row, $rowKey, $dimID)

    Called when group values between two rows are not equal. Each group has
    its own groupHeader. 

    Group headers are called from the changed group level down to the lowest
    declared group (within an data dimension).

    After executing all headers the detail action will be performed.

    :param mixed $groupValue: The current group value.
    :param array|object $row: The current row which triggered the group change.
    :param mixed $rowKey: The rowKey is the key of the current row taken from the input data set or given by calling the next() method.
    :param int $dimID: The current data dimension. Initial data dimension equals 0.
   
.. php:method:: groupFooter($groupValue, $row, $rowKey, $dimID)

    groupFooters are called like groupHeaders when group values between to rows
    are not equal. 
    
    But the footers are called from the lowest declared group (within a dimension)
    up to the changed group.

    The signature is the same as for groupHeader() methods but the values belongs
    to the last row within this group and **not** to the latest read row which triggered
    the group change.

.. php:method:: detail($row, $rowKey)

    Called for each row of the last data dimension. When the row triggered 
    a group change then the related group footers and group headers will be called before.

    :param array|object $row: The current row.
    :param mixed $rowKey: The rowKey is the key of the current row taken from the input data set or given by calling the next() method.


Methods for multi dimensional data
----------------------------------

Following methods belongs only to actions when multi dimensional data are declared
by the :php:meth:`::data` method.  

.. php:method:: noData_n()

    Called when the declared source for the next data dimension doesn't return any data.

.. php:method:: data_n($row, $rowKey, $dimID)

    Except for the last dimension this method is called for each data row (See detail method).   

    When group(s) are declared for this data dimension consider using groupHeader 
    and groupFooter methods instead. 

    :param array|object $row: The current row.
    :param mixed $rowKey: The rowKey is the key of the current row taken from the input data set or given by calling the next() method.
    :param int $dimID: The current data dimension. Initial data dimension equals 0.

.. php:method:: noGroupChange_n($row, $rowKey)

    Called when for a data dimension group(s) are declared but current row has the same group
    values than previous row.
    In a well designed data model this should not happen. If you can't change
    the model consider what you have to do in such situations.
    Ignoring this case, trigger a warning or throw an exception are valid options.

    :param array|object $row: The current row which triggered the group change.
    :param mixed $rowKey: The rowKey is the key of the current row taken from the input data set or given by calling the next() method.



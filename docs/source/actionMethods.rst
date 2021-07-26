Named events
............

Each named event is mapped to an action. 
Below all events are listed. Parameters passed to the action object are shown
in parenthesis. 

.. note: Not all actions make use of the parameters.  


Data independent events
----------------------- 

These methods are called even when no data are provided.  

init()
______
    First event. Use to initialize application properties independent
    from the __construct method.  

close()
_______        
    Last event. Use to clean up the dishes independent from __destruct method.

totalHeader()
_____________

    Called once to build the total header page of the report.

totalFooter()
_____________
    Called once to build the total footer page of the report.

Data driven events
------------------    

noData()
________
    This event only occurs when the given data set is empty. 
    In this case the following events will never be raised.


beforeGroupHeader($groupValue, $row, $rowKey)
_____________________________________________

    Raised before the group header action. To suppress any further actions
    return 'false'.  

    :param mixed $groupValue: The current group value.
    :param array|object $row: The current row which triggered the group change.
    :param mixed $rowKey: The rowKey is the key of the current row taken from the input data set or given by calling the next() method.

groupHeader($groupValue, $row, $rowKey)
_______________________________________________

    Raised when group values between two rows are not equal. Each group has
    its own groupHeader. 

    Group headers are executed from the changed group level down to the lowest
    declared group (within an data dimension).

    After executing all headers the detail event will be performed.

    :param mixed $groupValue: The current group value.
    :param array|object $row: The current row which triggered the group change.
    :param mixed $rowKey: The rowKey is the key of the current row taken from the input data set or given by calling the next() method.
   
groupFooter($groupValue, $row, $rowKey, $dimID)
_______________________________________________

    groupFooters are executed like groupHeaders when group values between to rows
    are not equal. 
    
    But the footers are called from the lowest declared group (within a dimension)
    up to the changed group.

    The signature is the same as for groupHeader() methods but the values belongs
    to the last row within this group and **not** to the latest read row which triggered
    the group change.

afterGroupFooter($groupValue, $row, $rowKey)
____________________________________________

    Raised after groupFooter. Might be used to handle the current output.
    Examples:
    Write outut to file and reset current output.
    Generate pdf file.
    Send output per mail.

beforeDetail($row, $rowKey)
___________________________

    Raised before detail actions. This is after the last afterGroupFooter action.
    Might be used to create header for details when groupFooter of last group
    is not suitable or to be more flexible.

detail($row, $rowKey)
_____________________

    Executed for each row of the last data dimension. When the row triggered 
    a group change then the related group footers and group headers will be called before.

    :param array|object $row: The current row.
    :param mixed $rowKey: The rowKey is the key of the current row taken from the input data set or given by calling the next() method.

afterDetail($row, $rowKey)
___________________________

    Raised after detail actions. 

Methods for multi dimensional data
----------------------------------

Following events belongs only to data sources having joined data.  

noData_n($dimID)
________________

    Called when the declared source for the next data dimension doesn't return any data.
    :param int $dimID: The ID of data dimension not having related data.

detail_n($row, $rowKey)
_______________________

    Except for the last dimension this event is raised for each data row (See detail method).   

    When group(s) are declared for this data dimension consider using groupHeader 
    and groupFooter methods instead. 

    :param array|object $row: The current row.
    :param mixed $rowKey: The rowKey is the key of the current row taken from the input data set or given by calling the next() method.

noGroupChange_n($row, $rowKey)
______________________________

    Raised when for a data dimension group(s) are declared but current row has the same group
    values than previous row.
    In a well designed data model this should not happen. If you can't change
    the model consider what you have to do in such situations.
    Ignoring this case, trigger a warning or throw an exception are valid options.

    :param array|object $row: The current row which triggered the group change.
    :param mixed $rowKey: The rowKey is the key of the current row taken from the input data set or given by calling the next() method.

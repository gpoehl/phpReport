Named events
............

Actions are identified by an ActionKey Enum. But you can always use the name
of the ActionKey. That allows usage of ActionKeys as keys in an array.

The executable action can be set during the initialisation process or by passing
specific parameters to certain methods.
 
Below is a list of all actions. Parameters passed to the action object are shown
in parenthesis.


Data independent events
-----------------------

These actions will always be executed:

Start()
_______
    First event. Use to initialize application properties independent
    from the __construct method.

Finish()
________
    Last event. Use to clean up the dishes independent from __destruct method.

TotalHeader()
_____________

    Called once to build the total header of the report.

TotalFooter()
_____________
    Called once to build the total footer of the report.

Data driven events
------------------

NoData()
________
    This event only occurs when the given data set is empty.


GroupBefore($groupValue, $row, $rowKey)
_______________________________________

    Raised before the group header action. To suppress any further actions
    return 'false'.

    :param mixed $groupValue: The current group value.
    :param array|object $row: The current row which triggered the group change.
    :param mixed $rowKey: The rowKey is the key of the current row taken from the input data set or given by calling the next() method.

GroupHeader($groupValue, $row, $rowKey)
_______________________________________

    Raised when group values between two rows are not equal. Each group has
    its own groupHeader.

    Group headers are executed from the changed group level down to the lowest
    declared group (within an data dimension).

    After executing all headers the detail event will be performed.

    :param mixed $groupValue: The current group value.
    :param array|object $row: The current row which triggered the group change.
    :param mixed $rowKey: The rowKey is the key of the current row taken from the input data set or given by calling the next() method.

GroupFooter($groupValue, $row, $rowKey, $dimID)
_______________________________________________

    groupFooters are executed like groupHeaders when group values between to rows
    are not equal.

    But the footers are called from the lowest declared group (within a dimension)
    up to the changed group.

    The signature is the same as for groupHeader() methods but the values belongs
    to the last row within this group and **not** to the latest read row which triggered
    the group change.

GroupAfter($groupValue, $row, $rowKey)
_______________________________________

    Raised after groupFooter. Might be used to handle the current output.
    Examples:
    Write outut to file and reset current output.
    Generate pdf file.
    Send output per mail.

DetailHeader($row, $rowKey)
___________________________

    Raised before detail actions. This is after the last afterGroupFooter action.
    Might be used to create header for details when groupFooter of last group
    is not suitable or to be more flexible.

Detail($row, $rowKey)
_____________________

    Executed for each row of the last data dimension. When the row triggered
    a group change then the related group footers and group headers will be called before.

    :param array|object $row: The current row.
    :param mixed $rowKey: The rowKey is the key of the current row taken from the input data set or given by calling the next() method.

DetailFooter($row, $rowKey)
___________________________

    Raised after detail actions.

Methods for multi dimensional data
----------------------------------

Following events belongs only to data sources having joined data.

DimNoData($dimID)
_________________

    Called when the declared source for the next data dimension doesn't return any data.
    :param int $dimID: The ID of data dimension not having related data.

DimDdetail($row, $rowKey)
_________________________

    Except for the last dimension this event is raised for each data row (See detail method).

    When group(s) are declared for this data dimension consider using groupHeader
    and groupFooter methods instead.

    :param array|object $row: The current row.
    :param mixed $rowKey: The rowKey is the key of the current row taken from the input data set or given by calling the next() method.

DimNoGroupChange($row, $rowKey, $dimID)
_______________________________________

    Raised only for rows not related to the last dimension and when
    group(s) are declared but current row don't trigger a group change.
    (Row has the same group values than previous row.)
    In most cases this is an unexpected behaviour and you might want to trigger
    an error. That's also the default behaviour.

    But sometimes it's deliberated (e.g. From a date field only the year or
    month is declared as a group) and you want to handle this non unique rows.

    :param array|object $row: The current row which triggered the group change.
    :param mixed $rowKey: The rowKey is the key of the current row taken from the input data set or given by calling the next() method.
    :param int $dimID: The ID of the current data dimension.
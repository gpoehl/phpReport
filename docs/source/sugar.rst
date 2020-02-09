Extra sugar
-----------

The report class has some extra methods to provide you with useful informations.

.. php:class:: Report

    The report class offers a lot of methods wich delivers almost all information
    you need to create your application.

    .. php:method:: getRow(int $dimID = null)

        Get the currently active row for the requested dimension. 

        :param int|null $dimID: The data dimension for which you want the current row. 
            Defaults to the current data dimension.  
            If $dimID is negative the value will be subtracted from the current 
            data dimension.
        :returns: The requested row.

    .. php:method:: getRowKey($dimID)

        Get the key of the currently active row for the requested dimension. 

        :param int|null $dimID: Same as in getRow(). 
        :returns: The requested key.

    .. php:method:: isFirst(): bool

            Checks if the current group is the first one within the next higer group.
            e.g. Is it the first invoice for a customer.

            :returns: True when the current group is the first one within
             the next higher level. False when not.

    .. php:method:: isLast(): bool

            Checks if the current group is the last one within the next higer group.
            The question can only be answered in group footers. 

            :returns: True when the current action is executed the last time within
             the next higher level. False when not.

    .. php:method:: getLevel($groupName = null): int

            Get the current group level or the level associated with the group name.

            :param string|null $groupName: The name of the group. Null for the
             current group level.

            :returns: The requested group level

    .. php:method:: getGroupValues(): array

            Get all active group values.
            Note that in footer methods the row which triggered the group 
            change is not yet active.

            :returns: Array having all values related to groups from first dimension to 
             current dimension.

    .. php:method:: getGroupValue($group = null)

            Get the current value for the requested group.

            :param int|null|string $group: String representing the group name
             or integer representing the group level. When null it defaults to 
             the current group level. Negative values are substracted from the
             current level. 

            :returns: Current value of the requested group.

    .. php:method:: getGroupNames(): array

            Get all group names.

            :returns array: Array of all group names.

    .. php:method:: getGroupName(int $groupLevel): string

            Get the associated group name of the group level.

            :param int $groupLevel: The level of the group.

            :returns string: The associated group name of the level.

    
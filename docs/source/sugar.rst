Methods returning information
-----------------------------

The report class has some extra convenience methods to provide you with useful information.

.. php:class:: Report

    The report class offers a lot of methods which delivers almost all information
    you need to create your application.

    .. php:method:: getRow(int $dimID = null)

        Get the active row for the requested dimension. 

        :param int|null $dimID: The data dimension for which you want the current row. 
            Defaults to the current data dimension.  
            If $dimID is negative the value will be subtracted from the current 
            data dimension.
        :returns: The active data row for the requested dimension.

    .. php:method:: getRowKey($dimID)

        Get key of the active row for the requested dimension. 

        :param int|null $dimID: Same as in getRow(). 
        :returns: The requested key.

    .. php:method:: isFirst($level = null): bool

            Test if the group at $level the first one within the next higher group.
            e.g. Is it the first invoice for a customer.

            :param string|int|null $level: The group level to be checked. Defaults
             to the current group or row when at detail level.

            :returns: True when the group at $level is the first one within
             the next higher level. False when not.

             :throws: InvalidArgumentException when level is below the current level.

    .. php:method:: isLast($level = null): bool

            Test if the group at $level is the last one within the next higher group.
            The test can only be executed in group footers. 

            :param string|int|null $level: The group level to be checked. Defaults
              to the level above the current level.

            :returns: True when the current group is the last one in all group
             levels between the group above the current level and $level.
             False when not.

            :throws: InvalidArgumentException when method is not called in a group footer
             or when $level is not above the current level.

    .. php:method:: getLevel($groupName = null): int

            Get the current group level or the level associated with the group name.

            :param string|null $groupName: The name of the group. Null for the
             current group level.

            :returns: The requested group level

    .. php:method:: getGroupValues(?int $dimID = null, bool $fromFirstLevel = true): array

            Get all active group values.
            Note that in footer methods the row which triggered the group 
            change is not yet active.

            :param int|null $dimID: The dimension id for / till the group values will be returned.
             Defaults to the current dimension id.

            :param bool $fromFirstLevel: When true all group values from the first 
             dimension to the requested dimension are returned. When false only the
             group values of the requested dimension are returned.

            :returns: Array with requested group values indexed by group level.

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

 .. php:method:: getDimId(mixed $level): int

            Get the dimension ID for a given group level.

            :param mixed $groupLevel: The level of the group. Defaults to the 
             current level.

            :returns int: The dimension id.

Overview of methods and properties
==================================

Methods to instantiate report class
-----------------------------------

:php:meth:`join` Declare next level of data to be joined or linked

:php:meth:`group` Declare a group to monitor changes between data rows

:php:meth:`compute` Declare variable to provide aggregate functons (sum, count, min, max)

:php:meth:`sheet` Declare variable to be aggregated horizontally (having key and value)

:php:meth:`fixedSheet` Like sheet but with pre-defined keys


Methods for data handling
-------------------------

:php:meth:`run` Start execution with data

:php:meth:`runPartial` Iterate over a set of data

:php:meth:`next` Take a single data row

:php:meth:`end` Finalize execution


Action methods called from report class
---------------------------------------

:php:meth:`init` First called method to initiialize application

:php:meth:`close` Last called method to clean up the dishes independent from __destruct method.

:php:meth:`totalHeader` Called once after init() to build the total header page of the report.

:php:meth:`totalFooter` Called once before close() to build the total footer page of the report.

:php:meth:`groupHeader` Called for each new group value(s)

:php:meth:`groupFooter` Called after detail() but before activating new group value(s).

:php:meth:`detail` Called for each data row in last data dimension.

:php:meth:`noData` Called when no data was given.

:php:meth:`noDataN` Called when no data was given for dimension 'n'.

:php:meth:`noGroupChangeN` Called when groups for dimension 'n' are declared but row didn't trigger a group change.


Methods returning information
-----------------------------

:php:meth:`getRow` Get the active row for the requested dimension.

:php:meth:`getRowKey` Get the key of active row for the requested dimension.

:php:meth:`getGroupNames` Get names for all declared groups.

:php:meth:`getGroupName` Get name for a requested or current group level.

:php:meth:`getGroupValues` Get current values for all declared groups.

:php:meth:`getGroupValue` Get current value for the requested or current group.

:php:meth:`getLevel` Get the current group level or the level associated with the group name.

:php:meth:`getChangedLevel` Get the level which triggered the group change.

:php:meth:`getDimID` Get the dimension id related to a group level or the current dimension id.

:php:meth:`isFirst` Bool if the action for the current or given level called the first time.

:php:meth:`isLast` Bool if the action for the current or given level called the last time.


Public Properties
-----------------

:$out:  Output object holding the output
:$gc:  Group count collector
:$rc:  Row count collector
:$total:  Collector for calculators, sheets and collectors
:$userConfig:  Configuration parameter given during instantiation

Prototyping methods
-------------------
:php:meth:`prototype` Call prototype method realted to current action.
:php:meth:`setCallAction` Alter targets for actions to be executed.
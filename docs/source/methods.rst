Overview of methods and properties
==================================

Methods to instantiate report class
-----------------------------------

:php:meth:`data` Describe data input

:php:meth:`group` Declare a group to monitor changes between data rows

:php:meth:`calculate` Declare variable to be calculated (sum, count, min, max)

:php:meth:`sheet` Declare variable to be calculated horizontally (having key and value)
 

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

:php:meth:`noData_n` Called when no data was given for dimension 'n'.

:php:meth:`noGroupChange_n` Called when groups for dimension 'n' are declared but row didn't trigger a group change.


Methods returning information
-----------------------------
getRow(int $dimID = null)

getRowKey(int $dimID = null)

getCurrentDimID()

prototype ()

setCallAction()

getGroupNames()

getGroupName(int $groupID = null)

getGroupValues()

getGroupValue($groupID = null)

getLevel()

getGroupLevel(string $groupName)

isFirst()

isLast()

Public Properties
-----------------

:$output:  String with concatenated return values from actions 
:$gc:  Group count collector
:$rc:  Row count collector
:$total:  Collector for calculated values, sheets and colloctors
:$userConfig:  Configuration parameter given during instantiation



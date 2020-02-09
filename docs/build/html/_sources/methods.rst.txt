Overview of methods and properties
==================================


Methods in phpReport class
--------------------------
data($source, $noData = null, $rowDetail = null, $noGroupChange = null, array $parameters = null)

group($name, $value = null, $headerAction = null, $footerAction = null)

calculate($name, $value = null, ?int $typ = self::XS, ?int $maxLevel = null)

sheet($name, $key, $value, ?int $typ = self::XS, $fromKey = null, $toKey = null, $maxLevel = null)

run(?iterable $data, bool $finalize = true)

next($row, $rowKey = null)

end()


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


Action methods called from report class
---------------------------------------


:init():  
:totalHeader():  
:totalFooter():
:groupHeader($groupValue, $row, $rowKey, $dimID):
:groupFooter($groupValue, $row, $rowKey, $dimID):
:detail($row, $rowKey):
:close():
:noData():
:noData_n ($dimID):
:noGroupChange_n ($row, $rowKey, $dimID):




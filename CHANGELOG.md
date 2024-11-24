phpReport 3.6
=============

This is the last version which runs with PHP 8.3 and later. 
Next version requires PHP 8.4


Changes
-------
* Support PHP 8.4
* Remove $isLastDim from Dimension class and handle last dimension ID in Report class. 


phpReport 3.5
=============

New features
------------
* Allow setting name for root data dimension.
* getRow() and getRowKey() now also accepts dimension name as parameter 
* New getDimIdOfGroup() method to get dimension by group name or group id. 
* New prototype class 'PrototypeMini' for program flow tests or for usage with
  phpunit assertions.

Changes
-------
* Alter Actionkey backed enums to pure enums.
* Remove some not required tests.
* Replace dimensions array by new array iterator class.
* Tests use new PrototypeMini class
* Getter for constant properties use new php 8.3 syntax


phpReport 3.4
=============

New features
------------

* Improved configuration. Two naming pattern sets are available.
* Configuration can be set on a global level by extendig report class.
* Actions use Action Enum instead of strings
* Data dimensions got additional name. Related method names can make use of the name.
* Action parameters for group() and join() methods had been replaced by one array parameter. PHP 8.2 support in mPDF 8.1.3
  This allows implementation of more actions.
* Bump phpUnit to version 11.

Bugfixes
--------




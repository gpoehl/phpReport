Configuration
=============

|project_name| comes with a set of default names and default behaviors which can
configurated to meet personal preferences or buisiness needs. 

Default names and classes:

.. php:attr:: totalName

    The name for the grand total group. 

    .. note:: You can always access grand totals by group level of 0.

.. php:attr:: detailName

    The name to be used in detail actions.

.. php:attr:: dimensionName

    The name of the root dimension.

.. php:attr:: outputHandler

    The classname of the default output handler

.. php:attr:: prototype

    The classname of the default prototype class

.. php:attr:: bool useNumberedActions

    'numberedActions' pattern will replace the 'defaultActions' patterns when value is true. 

Actions are usually mapped to a method in the application object but can also perform other tasks.
The following table lists possible actions and how they are declared.

.. csv-table:: Action types
   :header: "Action typ", "Action declared by"
   :widths: 50, 100

   "Call a method in target class", "Action is a legal method name"
   "Call a method in any class", "Action is an array ([class, method])"
   "Execute an callable", "Action is a callable"
   "Ouput a string", "Action is not a legal method name or begins with an :"
   "Throw an error", "Action is an array where second element equals to Action::ERROR"
   "Raise a warning", "Action is an array where second element equals to Action::WARNING"
   "Do nothing", "Action equals false"



Actions often contains placeholders to be replaced by


    .. list-table:: Action replacements
        :widths: auto
        :header-rows: 1

        * - Placeholder 
          - Replaced by
        * - %n
          - The group or data level ID
        * - %s
          - The group or data level name
        * - %S
          - ucfirst of the group or data level name


Whenever an defined event occurs the action assigned to an Actionkey will be
executed.

The following table lists all action keys with the assigned defaults.

    .. list-table:: Default actions
        :widths: auto
        :header-rows: 1

        * - Action key
          - Default action
        * - Start
          - start
        * - Finish
          - finish
        * - TotalHeader
          - header%S
        * - TotalFooter
          - footer%S
        * - GroupBefore
          - before%S
        * - GroupFirst
          - first%S
        * - GroupHeader
          - header%S
        * - GroupFooter
          - footer%S
        * - groupLast
          - last%S
        * - GroupAfter
          - after%S
        * - DetailHeader
          - header%S
        * - Detail
          - %s
        * - DetailFooter
          - footer%S
        * - NoData
          - <br><strong>No data found </strong><br>
        * - DimNoData
          - noData%S
        * - DimDetail
          - $this->detailName . %S
        * - DimNoGroupChange
          - | ["Current row in dimension %s didn't
            | trigger a group change.", Action::ERROR]

Next to the default actions above there is a second set of actions called
'numberedActions'. The intention of this set is to build method names on group
or data level id instead of their names. So applications might be written more
independent from the real data.

Configuration happens on several layers which allows a fine grained configuration.

At first there are defaults defined in the configurator class. To alter some or
all defaults on a global level there are three options:

1) Create a config file and pass the name of this file to the Report class.
2) Pass your modified defaults as an array to the Report class. This might be 
   done via data injection.
3) Create and use a new Report class which extends from the original one. Just
   override the string $configFilename with the name and location of your
   config file. 

Configuration from an config file will overwrite default configuration.
Config parameters passed to the report class will overwrite current configuration.
Config parameter 'action' will replace actions independend of the value of 
useNumberedActions parameter. 

Config parameters passed to certain methods like group() or join() will overwrite
configruation only for the related group or data dimension.

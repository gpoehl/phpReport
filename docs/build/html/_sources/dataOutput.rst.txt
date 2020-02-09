Data output
===========

The basic idea behind data input goes also for data output. |project_name| doesn't 
create any output itself. 

Even when the name of this library suggests to create a report you don't have to.

Write regular php code to do what ever fits your needs. Using external components like
mpdf, tcpfd, phpSpredsheet, PHPWord might help a lot.

|project_name| assists you collecting your output by appending the return values
of the action methods or defined action strings to the $output property.

$output is used for nothing else. So you can

    *  Alter the content
    *  Write content to disc
    *  Delete content
    *  Append data to content
    *  ...

Your action methods don't need to return any strings. You can also keep desired
output in any other variable.

.. note:: $content will be returned from the run() and the end() method.

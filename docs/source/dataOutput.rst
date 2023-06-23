Data output
===========

The basic idea behind data input is also true for data output. |project_name| doesn't 
create any output itself. 

Actions described before are the places where you can build your output.
Using external components like mpdf, tcpfd, phpSpredsheet, PHPWord will help 
to format the output as you like.

|project_name| assists you collecting your output by appending the return values
of the action methods or defined action strings to the $output property.

$output is used for nothing else. So you can

    *  Alter the content
    *  Write content to disc
    *  Delete content
    *  Append data to content
    *  ...

Your action methods don't need to return anything. You can also keep desired
output in any other variable.

.. note:: $content will be returned from the run() and the end() method.

<?php

/**
 * Configuration file.
 * Percent sign (%) in groupHeader and groupFooter will be replaced by a 
 * pattern build depending on $buildMethodsByGroupName rules.
 * Percent sign in totalHeader and totalFooter will be replaced by the value
 * of $grandTotalName.
 * Percent sign in noData_n will be replaced by number of dimension.
 * Values starting with : sign will handled as a string and not a a method name. 
 */
return [
    // Naming rule for groupHeader and groupFooter methods to be called
    // 'buildMethodsByGroupName' => true, // % will be replaced by group name (default)
    // 'buildMethodsByGroupName' => false, // % will be replaced by group number
    // 'buildMethodsByGroupName' => 'ucfirst', // % will be replaced by ucfirst of group name
    // 
    // Name of grand total group. Will also be used to replace % in totalHeader
    // and totalFooter actions.
    // 'grandTotalName' => 'total',
    // 
    // Actions to be performed when key events occurs
    'actions' => [
    // 'init' => 'init',
    // 'totalHeader' => '%Header',
    // 'groupHeader' => '%Header',
    // 'detail' => 'detail',
    // 'groupFooter' => '%Footer',
    // 'totalFooter' => '%Footer',
    // 'close' => 'close',
    // 
    // Action when job got not data
    // 'noData' => ':<br><strong>No data found</strong><br>', 
    // 
    // Action when data() method don't serve data.
    // 'noData_n' => 'noDataDim%',          
    // 
    // Action for each row in dimensions < last dimension. Usually not needed.
    // Group haeders and footers should be prefered.
    // Value false will not even call prototyp method.
    // 'detail_n' => false ,
    // 
    // Action for each row in dimensions < last dimension when groups are defined
    // but no group change was triggered.
    // This should happen only when data is not well normalized or might
    // happen when group attributes are not set properly.
    // To avoid this situation you might use the distinct option
    // with your SQL select statement or join data via a left join. 
    // You might also define a dummy group attribute.
    // To trigger a warning precede a text message with 'warning:'. To throw a
    // runTimeException precede a text message with 'error:'.
    // 
//     'noGroupChange_n' => ":Current row in dimension % didn't trigger a group change."
//     'noGroupChange_n' => "error:Current row in dimension % didn't trigger a group change."
       'noGroupChange_n' => 'noGroupChange%'
    
    ],
        // Optional parameter which might be used in owner class. Will not be used by phpReport itself.
        // 'userConfig' => null,
];





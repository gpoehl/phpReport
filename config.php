<?php

/**
 * Configuration file to replace default actions.
 * Percent sign (%) in beforeGroup, groupHeader, groupFooter and afterGroup will
 * be replaced by a pattern depending on $buildMethodsByGroupName rules.
 * Percent sign in totalHeader and totalFooter will be replaced by the value
 * of $grandTotalName.
 * Percent sign in noData_n, detail_n and nogroupChange_n will be replaced by 
 * the dimension ID.
 */
return [
    // Naming rule for groupHeader and groupFooter methods to be called
    // 'buildMethodsByGroupName' => true, // % will be replaced by group name (default)
    // 'buildMethodsByGroupName' => false, // % will be replaced by group number
    // 'buildMethodsByGroupName' => 'ucfirst', // % will be replaced by ucfirst of group name
    
    // Name of grand total group. Replaces also the % sing in totalHeader and totalFooter actions.
    // 'grandTotalName' => 'total',

    // Actions to be performed when key events occurs
    'actions' => [
    // 'init' => 'init',
    // 'totalHeader' => '%Header',
    // 'beforeGroup' => '%BeforeGroup',
    // 'groupHeader' => '%Header',
    // 'detail' => 'detail',
    // 'groupFooter' => '%Footer',
    // 'afterGroup' => '%AfterGroup',
    // 'totalFooter' => '%Footer',
    // 'close' => 'close',
  
    // Action when job got not data
    // 'noData' => ':<br><strong>No data found</strong><br>', 
  
    // Action when join() method don't serve data.
    // 'noData_n' => 'noDataDim%',          
    
    // Action for each row in dimensions < last dimension. Usually not needed.
    // Group haeders and footers should be prefered.
    // 'detail_n' => 'detai%',
     
    // Action for each row in dimensions < last dimension when groups are defined
    // but no group change was triggered.
    // This should happen only when data is not well normalized or might
    // happen when group attributes are not set properly.
    // To avoid this situation you might use the distinct option
    // with your SQL select statement or join data via a left join. 
    // You might also define a dummy group attribute.
   
//     'noGroupChange_n' => "Current row in dimension % didn't trigger a group change."
//     'noGroupChange_n' => ["Current row in dimension % didn't trigger a group change.",Action:ERROR]
       'noGroupChange_n' => 'noGroupChange%'
    ],
];





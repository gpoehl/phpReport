<?php

/**
 * Configuration file to replace default actions.
 * Percent sign (%) will be replaced
 * - in beforeGroup, groupHeader, groupFooter and afterGroup by a pattern
 * depending on $buildMethodsByGroupName rules.
 * - in totalHeader and totalFooter by the value of $grandTotalName.
 * - in noData_n, detail_n and nogroupChange_n by the dimension ID.
 */
return [
    // Naming rule for groupHeader and groupFooter methods to be called
    // 'buildMethodsByGroupName' => true, // % will be replaced by group name (default)
    // 'buildMethodsByGroupName' => false, // % will be replaced by group number
    // 'buildMethodsByGroupName' => 'ucfirst', // % will be replaced by ucfirst of group name
    //
    // Name of grand total group.
    // 'grandTotalName' => 'total',
    //
    // Actions to be performed when key events occurs
        'actions' => [
        // 'init' => 'init',
        // 'totalHeader' => '%Header',
        // 'beforeGroup' => 'before%',
        // 'groupHeader' => '%Header',
        // 'detail' => 'detail',
        // 'groupFooter' => '%Footer',
        // 'afterGroup' => 'after%',
        // 'totalFooter' => '%Footer',
        // 'close' => 'close',
        //
        // Action when job got not data
        // 'noData' => ':<br><strong>No data found</strong><br>',
        //
        // Action when join() method don't serve data.
        // 'noData_n' => 'noDataDim%',
        //
        // Action for each row in dimensions < last dimension. Usually not needed.
        // Group haeders and footers should be prefered.
        // 'detail_n' => 'detai%',
        //
        // Action only for rows not related to the last dimension and when
        // group(s) are declared but current row don't trigger a group change.
        // 'noGroupChange_n' => ["Current row in dimension % didn't trigger a group change.", Action:ERROR]
        // 'noGroupChange_n' => 'noGroupChange%'
    ],
];

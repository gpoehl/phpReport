<?php

/**
 * The % sign acts as a placeholder.
 * In groupHeader and groupFooter it will be replaced by the groupname or by level.
 * In fetechValues it will be replaced by the dimension.
 */
// 
return [
    'buildMethodsByGroupName' => true,  // false, true or 'ucfirst'
    'methods' => [
        'init' => 'init',
        'totalHeader' => 'totalHeader',
        'groupHeader' => '%Header',
        'detail' => 'detail',
        'groupFooter' => '%Footer',
        'totalFooter' => 'totalFooter',
        'close' => 'close',
        // : sign declares string explicid to avoid method calls when callOption = CALL_ALWAYS
        'noData' => ':<br><strong>No data found</strong><br>',   // Dimension = 0
        'noData_n' => 'noDataDim%',            // Dimension > 0 
    ],
    'userConfig' => null
];





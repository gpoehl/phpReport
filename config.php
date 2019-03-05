<?php

/**
 * The % sign acts as a placeholder.
 * In groupHeader and groupFooter it will be replaced by the groupname or by level.
 * In fetechValues it will be replaced by the dimension.
 */
// 
return [
    'buildMethodsByGroupName' => 'ucfirst', // false, true or 'ucfirst'
    'methods' => [
        'init' => 'init',
        'totalHeader' => 'header',
        'groupHeader' => 'header_%',
        'detail' => 'detail',
        'groupFooter' => 'footer_%',
        'totalFooter' => 'footer',
        'close' => 'close',
        'fetchValues' => 'fetchValues',      // Dimension = 0. 
        'fetchValues_n' => 'fetchValues_%',  // Dimension > 0. 
        // : sign declares string explicid to avoid method calls when callOption = CALL_ALWAYS
        'noData' => ':<br><strong>No data found</strong><br>',   // Dimension = 0
        'noData_n' => 'noData_%',            // Dimension > 0 
    ],
    'userConfig' => null
];





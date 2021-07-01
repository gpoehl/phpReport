<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
declare(strict_types=1);

namespace gpoehl\phpReport\output;

/**
 *
 * @author Günter
 */
abstract class AbstractOutput
{
     public array $actionKeyMapper = [
        'init' => null,
        'totalHeader' => null,
        'beforeGroup' => null,
        'groupHeader' => null,
         'detailHeader' => null,
        'detailFooter' => null,
         'detail' => null,
        'groupFooter' => null,
        'afterGroup' => null,
        'totalFooter' => null,
        'close' => null,
        'noData' => null,
        'noData_n' => null,
        'detail_n' => null,
        'noGroupChange_n' => null,
    ];

    public function __construct(public string $separator = '') {
        
    }

    public function setSeparator(string $separator) {
        $this->separator = $separator;
    }

    abstract public function write($value, int $level, $key);

    abstract public function prepend($value, int $level, string $key);

    abstract public function get(?int $level = null, ?string $key = null);

    abstract public function pop(?int $level = null, ?string $key = null);

    abstract public function delete(?int $level = null, ?string $key = null);

    abstract public function __toString();
}

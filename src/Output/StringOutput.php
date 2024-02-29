<?php

/*
 * This file is part of the gpoehl/phpReport library.
 *
 * @license   GNU LGPL v3.0 - For details have a look at the LICENSE file
 * @copyright © Günter Pöhl
 * @link      https://github.com/gpoehl/phpReport/readme
 * @author    Günter Pöhl  <phpReport@gmx.net>
 */

declare(strict_types=1);

namespace gpoehl\phpReport\Output;

/**
 * Basic output class
 * Output is managed as plain string.
 * Parameter for level and subkey are ignored.
 */
class StringOutput extends AbstractOutput {

    public function __construct(public string $separator = '') {
        $this->actionKeyMapper = new \WeakMap();
        foreach (\gpoehl\phpReport\Actionkey::cases() as $case) {
            $this->actionKeyMapper[$case] = 0;
        }
    }

    private $output = '';

    public function write($value, ?int $level = null, ?int $key = null) {
        $this->output .= $value . $this->separator;
    }

    public function prepend($value, ?int $level = null, ?int $key = null) {
        $this->output = $value . $this->separator . $this->output;
    }

    public function get(?int $level = null, ?int $key = null): ?string {
        if (empty($this->separator) || empty($this->output)) {
            return $this->output;
        }
        return substr($this->output, 0, -strlen($this->separator));
    }

    public function pop(?int $level = null, $key = null): ?string {
        $wrk = $this->get();
        $this->output = '';
        return $wrk;
    }

    public function delete(?int $level = null, ?int $key = null): void {
        $this->output = '';
    }

    public function __toString() {
        return $this->get();
    }
}

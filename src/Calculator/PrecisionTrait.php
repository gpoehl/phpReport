<?php

/*
 * This file is part of the gpoehl/phpReport library.
 *
 * @license   GNU LGPL v3.0 - For details have a look at the LICENSE file
 * @copyright ©2019 Günter Pöhl
 * @link      https://github.com/gpoehl/phpReport/readme
 * @author    Günter Pöhl  <phpReport@gmx.net>
 */
declare(strict_types=1);

namespace gpoehl\phpReport\Calculator;

/*
 * Precision for Bcm Calculators and collectors
 */

Trait PrecisionTrait
{

   /** @var $zero Zero value string to represent arbitrary precision numbers */
    private string $zero;
    /** @var $scale Null indicates that collectors don't use BCMath methods. */
    protected ?int $scale = null;

    /**
     * Set the default scale parameter for all math functions in collectors
     * and bcm calculators.
     * Note: When scale is set you can't reset it to Null again.
     * This value will be used with all math functions and to set zero strings
     * @param int|null $scale Null to use value of bcscale().
     */
    public function setScale(? int $scale = null):void {
        $this->scale =  $scale ?? bcscale($scale);
        $this->zero = str_pad('0.', 2 + $this->scale, '0');
    }

    public function getScale(): ?int {
        return $this->scale;
    }


}

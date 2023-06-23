<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace gpoehl\phpReport\Output;

/**
 * Description of Band
 *
 * @author gpoeh
 */
enum Band: int {
    case HeaderTop = 1;
    case Header = 2;
    case HeaderBottom = 3;
    case DataTop = 4;
    case Data = 5;
    case DataBottom = 6;
    case FooterTop = 7;
    case Footer = 8;
    case FooterBottom = 9;
}


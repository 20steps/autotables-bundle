<?php
/**
 * AutoTablesBundle
 * Copyright (c) 2014, 20steps Digital Full Service Boutique, All rights reserved.
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3.0 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library.
 */


namespace twentysteps\Bundle\AutoTablesBundle\Twig;


/**
 * Enumeration of the possible Frontend Frameworks supported.
 *
 * TODO Should be switched to http://de1.php.net/manual/en/class.splenum.php as soon as possible
 */
final class FrontendFramework {

    const STANDARD = 0;
    const BOOTSTRAP3 = 1;

    private function __construct() {
        // no instantiation possible
    }

    public static function fromString($str) {
        $rtn = null;
        switch ($str) {
            case "standard":
                $rtn = FrontendFramework::STANDARD;
                break;
            case "bootstrap3":
                $rtn = FrontendFramework::BOOTSTRAP3;
                break;
            default:
                throw new \InvalidArgumentException('Illegal frontend framework ['.$str.']');
        }
        return $rtn;
    }

    public static function toString($val) {
        $rtn = null;
        switch ($val) {
            case FrontendFramework::STANDARD:
                $rtn = 'standard';
                break;
            case FrontendFramework::BOOTSTRAP3:
                $rtn = 'bootstrap3';
                break;
            default:
                throw new \InvalidArgumentException('Illegal frontend framework value ['.$val.']');
        }
        return $rtn;
    }
} 
<?php
/**
 * DataTablesBundle
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

namespace twentysteps\Bundle\DataTablesBundle\Util;


class Ensure
{

    public static function ensureNotNull($value, $format, $args = null, $_ = null)
    {
        if ($value == null) {
            throw new \LogicException(sprintf($format, $args, $_));
        }
    }

    public static function ensureNotEmpty($value, $format, $args = null, $_ = null)
    {
        if (!$value) {
            throw new \LogicException(sprintf($format, $args, $_));
        }
    }

    public static function ensureNull($value, $format, $args = null, $_ = null)
    {
        if ($value != null) {
            throw new \LogicException(sprintf($format, $args, $_));
        }
    }

    public static function ensureTrue($value, $format, $args = null, $_ = null)
    {
        if (!$value) {
            throw new \LogicException(sprintf($format, $args, $_));
        }
    }

    public static function ensureFalse($value, $format, $args = null, $_ = null)
    {
        if ($value) {
            throw new \LogicException(sprintf($format, $args, $_));
        }
    }

    public static function ensureEquals($expected, $value, $format, $args = null, $_ = null)
    {
        if ($expected != $value) {
            throw new \LogicException(sprintf($format, $args, $_));
        }
    }

}
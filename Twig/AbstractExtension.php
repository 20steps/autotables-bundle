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
use InvalidArgumentException;
use twentysteps\Bundle\AutoTablesBundle\Util\Ensure;
use utilphp\util;

/**
 * Extension base class offering a render method.
 */
abstract class AbstractExtension extends \Twig_Extension
{

    private $environment;

    public function initRuntime(\Twig_Environment $environment)
    {
        $this->environment = $environment;
    }

    public function render($templateName, $context)
    {
        return $this->environment->render($templateName, $context);
    }

    /**
     * Tries to find parameter named $key in the $args array and throws an InvalidArgumentException
     * if not found.
     */
    protected function getRequiredParameter($args, $key)
    {
        $value = util::array_get($args[$key]);
        if (!$value) {
            throw new InvalidArgumentException('Missing parameter: '.$key);
        }
        return $value;
    }

    /**
     * Tries to find the optional parameter $key in the $args array and returns the $defaultValue if
     * not found.
     */
    protected function getParameter($args, $key, $defaultValue = NULL)
    {
        $value = util::array_get($args[$key]);
        return is_null($value) ? $defaultValue : $value;
    }

} 
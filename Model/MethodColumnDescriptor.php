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

namespace twentysteps\Bundle\AutoTablesBundle\Model;

class MethodColumnDescriptor extends AbstractColumnDescriptor
{

    /**
     * @var \ReflectionMethod
     */
    private $getterMethod;

    /**
     * @var \ReflectionMethod
     */
    private $setterMethod;

    public function __construct(\ReflectionMethod $getterMethod)
    {
        parent::__construct('p' . $getterMethod->getName(), $getterMethod->getName());
        $this->getterMethod = $getterMethod;
        $this->getterMethod->setAccessible(TRUE);
    }

    /**
     * @param \ReflectionMethod $setterMethod
     */
    public function setSetterMethod($setterMethod) {
        $this->setterMethod = $setterMethod;
        if ($this->setterMethod) {
            $this->setterMethod->setAccessible(TRUE);
        }
    }


    public function getValue($entity)
    {
        $value = null;
        if ($this->getterMethod) {
            $value = $this->getterMethod->invoke($entity);
        }
        return $value;
    }

    public function setValue($entity, $value)
    {
        if ($this->setterMethod) {
            $this->setterMethod->invoke($entity, $value);
        }
    }
} 
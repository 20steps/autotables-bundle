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


class PropertyColumnDescriptor extends AbstractColumnDescriptor
{
    /**
     * @var \ReflectionProperty
     */
    private $property;

    /**
     * @var int
     */
    private $order;

    public function __construct(\ReflectionProperty $property)
    {
        parent::__construct('p' . $property->getName(), $property->getName());
        $this->property = $property;
        $this->property->setAccessible(TRUE);
    }

    public function getValue($entity)
    {
        $value = null;
        if ($this->property) {
            $value = $this->property->getValue($entity);
        }
        return $value;
    }

    public function setValue($entity, $value)
    {
        if ($this->property) {
            $value = $this->property->setValue($entity, $value);
        }
    }
} 
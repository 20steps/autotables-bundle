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

namespace twentysteps\Bundle\DataTablesBundle\Model;


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

    public function __construct($columnId, $name, $type, $order, \ReflectionProperty $property)
    {
        parent::__construct($columnId, $name, $type, $order);
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
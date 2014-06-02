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


class EntityDescriptor
{

    private $columnDescriptors;
    private $idProperty;

    public function __construct($columnDescriptors, \ReflectionProperty $idProperty)
    {
        $this->columnDescriptors = $columnDescriptors;
        $this->idProperty = $idProperty;
        if ($this->idProperty) {
            $this->idProperty->setAccessible(TRUE);
        }
    }

    /**
     * @return AbstractColumnDescriptor[]
     */
    public function getColumnDescriptors()
    {
        return $this->columnDescriptors;
    }

    public function fetchId($entity)
    {
        $id = null;
        if ($this->idProperty) {
            $id = $this->idProperty->getValue($entity);
        }
        return $id;
    }
}
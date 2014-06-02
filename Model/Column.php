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

/**
 * Representation of a DataTables column found inside a Doctrine entity.
 * @package twentysteps\Bundle\DataTablesBundle\Model
 */
class Column
{

    /**
     * @var AbstractColumnDescriptor
     */
    private $columnDescriptor;

    /**
     * @var mixed
     */
    private $value;

    public function __construct(AbstractColumnDescriptor $columnDescriptor, $value)
    {
        $this->columnDescriptor = $columnDescriptor;
        $this->value = $value;
    }

    public function getColumnDescriptorId()
    {
        return $this->columnDescriptor->getId();
    }

    public function getName()
    {
        return $this->columnDescriptor->getName();
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->columnDescriptor->getType();
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return int
     */
    public function getOrder()
    {
        return $this->columnDescriptor->getOrder();
    }

    public function getEntityClassName()
    {
        return $this->columnDescriptor->getEntityClassName();
    }
}
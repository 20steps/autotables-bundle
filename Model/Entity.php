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


class Entity
{
    private $id;
    private $entityDescriptor;
    private $columns;
    private $obj;

    public function __construct($id, EntityDescriptor $entityDescriptor, &$columns, $obj)
    {
        $this->id = $id;
        $this->entityDescriptor = $entityDescriptor;
        $this->columns = $columns;
        $this->obj = $obj;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @return mixed
     */
    public function getObj()
    {
        return $this->obj;
    }


}
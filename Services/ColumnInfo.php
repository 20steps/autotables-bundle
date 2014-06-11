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


namespace twentysteps\Bundle\AutoTablesBundle\Services;

use Doctrine\ORM\Mapping as ORM;
use twentysteps\Bundle\AutoTablesBundle\Annotations as AUT;
use twentysteps\Bundle\AutoTablesBundle\DependencyInjection\AutoTablesConfiguration;
use utilphp\util;

/**
 * Gathers information about the column representation of a property resp. getter method.
 */
class ColumnInfo {

    private $name;
    private $type;
    private $order = 10000;
    private $readOnly = false;
    private $ignore = false;

    public function __construct($name) {
        $this->name = $name;
    }

    public function addORMAnnotation(ORM\Column $column = null) {
        if ($column) {
            $this->name = $column->name ? : $this->name;
            $this->type = $column->type ? : $this->type;
        }
    }

    public function addAutoTablesAnnotation(AUT\Column $column = null) {
        if ($column) {
            $this->name = $column->getName() ? : $this->name;
            $this->type = $column->getType() ? : $this->type;
            $this->order = $column->getOrder();
            $this->readOnly = $column->isReadOnly();
            $this->ignore = $column->isIgnore();
        }
    }

    public function addAutoTablesConfig(AutoTablesConfiguration $config, $selector) {
        $columnOverwrite = util::array_get($config->getColumns()[$selector]);
        if ($columnOverwrite) {
            $this->readOnly = util::array_get($columnOverwrite['readOnly'], $this->readOnly);
            $this->name = util::array_get($columnOverwrite['name'], $this->name);
            $this->type = util::array_get($columnOverwrite['type'], $this->type);
            $this->order = util::array_get($columnOverwrite['order'], $this->order);
            $this->ignore = util::array_get($columnOverwrite['ignore'], $this->ignore);
        }
    }

    /**
     * Returns true, if the column should be used in auto generated tables.
     */
    public function isUsable() {
        return !$this->ignore && $this->name && $this->type;
    }

    /**
     * @return boolean
     */
    public function isIgnore() {
        return $this->ignore;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getOrder() {
        return $this->order;
    }

    /**
     * @return boolean
     */
    public function isReadOnly() {
        return $this->readOnly;
    }

    /**
     * @return string
     */
    public function getType() {
        return $this->type;
    }
}
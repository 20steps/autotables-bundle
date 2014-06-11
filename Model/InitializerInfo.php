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

use twentysteps\Bundle\AutoTablesBundle\Annotations as AUT;

/**
 * Gathers information about a column's initializer.
 */
class InitializerInfo {

    private $repository;
    private $id;
    private $value;

    public function addInitializerAnnotation(AUT\Initializer $initializer = null) {
        if ($initializer) {
            $this->repository = $initializer->getRepository() ? : $this->repository;
            $this->id = $initializer->getId() ? : $this->id;
            $this->value = $initializer->getValue() ? : $this->value;
        }
    }

    /**
     * @return mixed
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getRepository() {
        return $this->repository;
    }

    /**
     * @return mixed
     */
    public function getValue() {
        return $this->value;
    }
}
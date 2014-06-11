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

namespace twentysteps\Bundle\AutoTablesBundle\Annotations;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target({"ANNOTATION"})
 */
final class Initializer extends Annotation {

    /**
     * Sets the id of the repository to be used for initializing a column
     * @var string
     */
    public $repository;

    /**
     * Sets the value of the id to be searched in the given repository.
     * @var mixed
     */
    public $id;

    /**
     * Sets a constant value to be injected into the column.
     * @var mixed
     */
    public $value;

    /**
     * @return string
     */
    public function getRepository() {
        return $this->repository;
    }

    /**
     * @return mixed
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getValue() {
        return $this->value;
    }
}
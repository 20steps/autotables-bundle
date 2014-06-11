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
 * @Target({"METHOD","PROPERTY"})
 */
final class Column extends Annotation {
    /**
     * Sets the type of the annotated column.
     * @var string
     */
    public $type;

    /**
     * Sets the name of the annotated column, which may be translated later.
     * @var string
     */
    public $name;

    /**
     * Selects the ordering of the column. Smaller values will come first.
     * @var int
     */
    public $order = 10000;

    /**
     * Sets whether the column should be immutable.
     * @var boolean
     */
    public $readOnly;

    /**
     * Simply ignores the annotated property/method if set to true. Ignored columns won't
     * be displayed in the auto generated tables.
     * @var boolean
     */
    public $ignore;

    /**
     * @var Initializer
     */
    public $initializer;

    /**
     * @return string
     */
    public function getType() {
        return $this->type;
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
     * @return boolean
     */
    public function isIgnore() {
        return $this->ignore;
    }

    /**
     * @return Initializer
     */
    public function getInitializer() {
        return $this->initializer;
    }
}
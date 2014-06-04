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

/**
 * Access point for the AutoTablesBundle to perform any needed CRUD operation for a given
 * entity type.
 */
interface AutoTablesCrudService
{
    /**
     * Simply returns a fresh entity for inserting values.
     */
    public function createEntity();

    /**
     * Returns the entity with the given id or null if not found.
     */
    public function findEntity($id);

    /**
     * Persists the given entity. The id may be still null, if this is the first time
     * the entity is persisted.
     */
    public function persistEntity($entity);

    /**
     * Removes the given entity from persistency.
     */
    public function removeEntity($entity);
} 
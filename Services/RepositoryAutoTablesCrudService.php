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

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;

/**
 * Adapter for wrapping a repository to a AutoTablesCrudService.
 */
class RepositoryAutoTablesCrudService implements AutoTablesCrudService {

    private $em;
    private $repository;
    private $reflClass;

    public function __construct(ObjectManager $em, ObjectRepository $repository) {
        $this->em = $em;
        $this->repository = $repository;
        $this->reflClass = new \ReflectionClass($repository->getClassName());
    }

    /**
     * Returns the class name for the entity.
     */
    public function getEntityClassName() {
        return $this->repository->getClassName();
    }


    public function createEntity()
    {
        return $this->reflClass->newInstance();
    }

    public function findEntity($id)
    {
        return $this->repository->find($id);
    }

    public function persistEntity($entity)
    {
        $this->em->persist($entity);
        $this->em->flush();
    }

    public function removeEntity($entity)
    {
        $this->em->remove($entity);
        $this->em->flush();
    }
}
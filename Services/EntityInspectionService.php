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

namespace twentysteps\Bundle\DataTablesBundle\Services;

use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Bridge\Monolog\Logger;
use twentysteps\Bundle\DataTablesBundle\Model\AbstractColumnDescriptor;
use twentysteps\Bundle\DataTablesBundle\Model\Column;
use twentysteps\Bundle\DataTablesBundle\Model\Entity;
use twentysteps\Bundle\DataTablesBundle\Model\EntityDescriptor;
use twentysteps\Bundle\DataTablesBundle\Model\MethodColumnDescriptor;
use twentysteps\Bundle\DataTablesBundle\Model\PropertyColumnDescriptor;
use twentysteps\Bundle\DataTablesBundle\Util\Ensure;
use utilphp\util;
use Stringy\StaticStringy;

/**
 * Service for inspecting entity classes and returning lists of Column descriptions to
 * be used for DataTables.
 */
class EntityInspectionService
{
    private $reader;
    private $entityDescriptorMap;
    private $columnDescriptorMap;

    public function __construct()
    {
        $this->reader = new AnnotationReader();
        $this->entityDescriptorMap = array();
        $this->columnDescriptorMap = array();
    }

    /**
     * Inspects the given entities and returns a list of Entity objects for each of them.
     */
    public function parseEntities($entities)
    {
        $entityList = array();
        foreach ($entities as $entity) {
            $entityList[] = $this->parseEntity($entity);
        }
        return $entityList;
    }

    /**
     * Inspects the given entity and returns an Entity object for it.
     * @return Entity
     */
    public function parseEntity($entity)
    {
        $reflClass = new \ReflectionClass($entity);
        $entityDescriptor = util::array_get($this->entityDescriptorMap[$reflClass->getName()]);
        if (!$entityDescriptor) {
            $entityDescriptor = $this->initDescriptors($reflClass, $entity);
        }
        $columns = array();
        foreach ($entityDescriptor->getColumnDescriptors() as $columnDescriptor) {
            /* @var $columnDescriptor AbstractColumnDescriptor */
            $columns[] = new Column($columnDescriptor, $columnDescriptor->getValue($entity));
        }
        usort($columns, function (Column $a, Column $b) {
            return $a->getOrder() - $b->getOrder();
        });
        return new Entity($entityDescriptor->fetchId($entity), $entityDescriptor, $columns);
    }

    /**
     * Updates the specified value in the given entity.
     */
    public function setValue($entity, $columnDescriptorId, $value)
    {
        Ensure::ensureNotNull($entity, 'entity musst not be null');
        $columnDescriptor = $this->getColumnDescriptor($entity, $columnDescriptorId);
        $columnDescriptor->setValue($entity, $value);
    }

    public function getValue($entity, $columnDescriptorId)
    {
        Ensure::ensureNotNull($entity, 'entity musst not be null');
        $columnDescriptor = $this->getColumnDescriptor($entity, $columnDescriptorId);
        return $columnDescriptor->getValue($entity);
    }

    // TODO there should be a better solution than using this... We want caching!
    public function fetchId($entity)
    {
        $id = null;
        $idProperty = $this->fetchIdProperty(new \ReflectionClass($entity));
        if ($idProperty) {
            $idProperty->setAccessible(TRUE);
            $id = $idProperty->getValue($entity);
        }
        return $id;
    }

    private function getColumnDescriptor($entity, $columnDescriptorId)
    {
        $columnDescriptor = util::array_get($this->columnDescriptorMap[$columnDescriptorId]);
        if (!$columnDescriptor) {
            $this->initDescriptors(new \ReflectionClass($entity), $entity);
            $columnDescriptor = util::array_get($this->columnDescriptorMap[$columnDescriptorId]);
            Ensure::ensureNotNull($columnDescriptor, 'Failed to load column [%s] for entity of type [%s]', $columnDescriptorId, get_class($entity));
        }
        return $columnDescriptor;
    }

    private function initDescriptors(\ReflectionClass $reflClass, $entity)
    {
        $columnDescriptors = array();
        $this->parsePropertyColumnDescriptors($reflClass, $columnDescriptors);
        $this->parseMethodColumnDescriptors($reflClass, $columnDescriptors);
        $entityDescriptor = new EntityDescriptor($columnDescriptors, $this->fetchIdProperty($reflClass));
        $this->entityDescriptorMap[$reflClass->getName()] = $entityDescriptor;
        return $entityDescriptor;
    }

    private function parsePropertyColumnDescriptors(\ReflectionClass $reflClass, &$columnDescriptors)
    {
        foreach ($reflClass->getProperties() as $property) {
            $name = null;
            $type = null;
            $order = 0;
            $ignore = false;
            foreach ($this->reader->getPropertyAnnotations($property) as $annot) {
                if (($annot instanceof \twentysteps\Bundle\DataTablesBundle\Annotations\ColumnMeta) ||
                    ($annot instanceof \Doctrine\ORM\Mapping\Column and !$name)
                ) {
                    $name = $annot->name ? : $property->getName();
                    $type = $annot->type ? : $type;
                    if ($annot instanceof \twentysteps\Bundle\DataTablesBundle\Annotations\ColumnMeta) {
                        $order = $annot->order ? : 0;
                    }
                }
                if ($annot instanceof \twentysteps\Bundle\DataTablesBundle\Annotations\ColumnIgnore) {
                    $ignore = true;
                    break;
                }
            }
            if (!$ignore && $name && $type) {
                $columnDescriptor = new PropertyColumnDescriptor('p' . $property->getName(), $name, $type, $order, $property);
                $this->columnDescriptorMap[$columnDescriptor->getId()] = $columnDescriptor;
                $columnDescriptors[] = $columnDescriptor;
            }
        }
    }

    private function parseMethodColumnDescriptors(\ReflectionClass $reflClass, &$columnDescriptors)
    {
        foreach ($reflClass->getMethods() as $method) {
            $name = null;
            $type = null;
            $order = 0;
            $ignore = false;
            foreach ($this->reader->getMethodAnnotations($method) as $annot) {
                if ($annot instanceof \twentysteps\Bundle\DataTablesBundle\Annotations\ColumnMeta) {
                    Ensure::ensureTrue(count($method->getParameters()) == 0, 'Failed to use [%s] as getter method, only parameterless methods supported for @ColumnMeta', $method->getName());
                    Ensure::ensureTrue(StaticStringy::startsWith($method->getName(), 'get'), 'Illegal method name [%s], getter methods must start with a get prefix', $method->getName());
                    $name = $annot->getName() ? : $method->getName();
                    $type = $annot->getType() ? : $type;
                    $order = $annot->getOrder() ? : 0;
                }
                if ($annot instanceof \twentysteps\Bundle\DataTablesBundle\Annotations\ColumnIgnore) {
                    $ignore = true;
                    break;
                }
            }
            if (!$ignore && $name && $type) {
                $setterMethod = $reflClass->getMethod('set' . substr($method->getName(), 3));
                if ($setterMethod) {
                    Ensure::ensureEquals(1, count($setterMethod->getParameters()), 'setter method [%s] needs to have exactly one parameter', $setterMethod->getName());
                }
                $columnDescriptor = new MethodColumnDescriptor('m' . $method->getName(), $name, $type, $order, $method, $setterMethod);
                $this->columnDescriptorMap[$columnDescriptor->getId()] = $columnDescriptor;
                $columnDescriptors[] = $columnDescriptor;
            }
        }
    }

    private function fetchIdProperty(\ReflectionClass $reflClass)
    {
        $idProperty = null;
        foreach ($reflClass->getProperties() as $property) {
            foreach ($this->reader->getPropertyAnnotations($property) as $annot) {
                if ($annot instanceof \Doctrine\ORM\Mapping\Id) {
                    $idProperty = $property;
                    break;
                }
            }
        }
        return $idProperty;
    }
}
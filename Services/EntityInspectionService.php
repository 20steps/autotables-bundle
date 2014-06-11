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

use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\HttpFoundation\RequestStack;
use twentysteps\Bundle\AutoTablesBundle\DependencyInjection\AutoTablesConfiguration;
use twentysteps\Bundle\AutoTablesBundle\Model\AbstractColumnDescriptor;
use twentysteps\Bundle\AutoTablesBundle\Model\Column;
use twentysteps\Bundle\AutoTablesBundle\Model\ColumnInfo;
use twentysteps\Bundle\AutoTablesBundle\Model\Entity;
use twentysteps\Bundle\AutoTablesBundle\Model\EntityDescriptor;
use twentysteps\Bundle\AutoTablesBundle\Model\MethodColumnDescriptor;
use twentysteps\Bundle\AutoTablesBundle\Model\PropertyColumnDescriptor;
use twentysteps\Bundle\AutoTablesBundle\Util\Ensure;
use utilphp\util;
use Stringy\StaticStringy;

/**
 * Service for inspecting entity classes and returning lists of Column descriptions to
 * be used for DataTables.
 */
class EntityInspectionService {

    private $reader;
    private $entityDescriptorMap;
    private $columnDescriptorMap;
    private $translator;
    private $logger;
    private $requestStack;
    private $doctrine;

    public function __construct($translator, $logger, RequestStack $requestStack, $doctrine) {
        $this->reader = new AnnotationReader();
        $this->entityDescriptorMap = array();
        $this->columnDescriptorMap = array();
        $this->translator = $translator;
        $this->logger = $logger;
        $this->requestStack = $requestStack;
        $this->doctrine = $doctrine;
    }

    /**
     * Inspects the given entities and returns a list of Entity objects for each of them.
     */
    public function parseEntities($entities, AutoTablesConfiguration $config) {
        $entityList = array();
        foreach ($entities as $entity) {
            $entityList[] = $this->parseEntity($entity, $config);
        }
        return $entityList;
    }

    /**
     * Inspects the given entity and returns an Entity object for it.
     * @return Entity
     */
    public function parseEntity($entity, AutoTablesConfiguration $config) {
        $entityDescriptor = $entityDescriptor = $this->fetchEntityDescriptor($entity, $config);
        $columns = array();
        foreach ($entityDescriptor->getColumnDescriptors() as $columnDescriptor) {
            /* @var $columnDescriptor AbstractColumnDescriptor */
            $columns[] = new Column($columnDescriptor, $columnDescriptor->getValue($entity));
        }
        usort($columns, function (Column $a, Column $b) {
            return $a->getOrder() - $b->getOrder();
        });
        return new Entity($entityDescriptor->fetchId($entity), $entityDescriptor, $columns, $entity);
    }

    /**
     * Executes any initializer configured for the given entity.
     */
    public function initializeEntity($entity, AutoTablesConfiguration $config) {
        $entityDescriptor = $this->fetchEntityDescriptor($entity, $config);
        foreach ($entityDescriptor->getColumnDescriptors() as $column) {
            $this->logger->info(sprintf('initialize column [%s]', $column->getName()));
            if ($column->getInitializer()) {
                $this->logger->info(sprintf('initialize column has initializer'));
                $value = null;
                if ($column->getInitializer()->getValue()) {
                    $value = $column->getInitializer()->getValue();
                } else if ($column->getInitializer()->getRepository()) {
                    $id = $column->getInitializer()->getId();
                    if (!$id) {
                        // search the id in the request
                        $request = $this->requestStack->getCurrentRequest();
                        if ($request) {
                            $id = $request->get('id'.$column->getId());
                        }
                    }
                    Ensure::ensureNotNull($id, 'Missing id for column [%s] in [%s]', $column->getName(), $config->getId());
                    $repository = $this->doctrine->getRepository($column->getInitializer()->getRepository());
                    Ensure::ensureNotNull($repository, 'Repository with id [%s] not found for [%s]', $column->getInitializer()->getRepository(), $config->getId());
                    $value = $repository->find($id);
                    //$this->logger->info(sprintf('Inject [%s] for [%s]', print_r($value, true), $column->getName()));
                }
                $this->setValue($entity, $column->getId(), $value, $config);
                //$this->logger->info(sprintf('Entity is [%s]', print_r($entity, true)));
            }
        }
    }

    /**
     * Updates the specified value in the given entity.
     */
    public function setValue($entity, $columnDescriptorId, $value, AutoTablesConfiguration $config) {
        Ensure::ensureNotNull($entity, 'entity musst not be null');
        $columnDescriptor = $this->getColumnDescriptor($entity, $columnDescriptorId, $config);
        if ($columnDescriptor->getType() == 'datetime') {
            $format = $this->translator->trans('php.date.format', array(), $config->getTransScope());
            $date = \DateTime::createFromFormat($format, $value);
            //$this->logger->info(sprintf('Created date [%s] from value [%s] with format [%s]', $date, $value, $format));
            $columnDescriptor->setValue($entity, $date);
        } else {
            $columnDescriptor->setValue($entity, $value);
        }
    }

    public function getValue($entity, $columnDescriptorId, AutoTablesConfiguration $config) {
        Ensure::ensureNotNull($entity, 'entity musst not be null');
        $columnDescriptor = $this->getColumnDescriptor($entity, $columnDescriptorId, $config);
        $value = $columnDescriptor->getValue($entity);
        $rtn = $value;
        if ($columnDescriptor->getType() == 'datetime') {
            $format = $this->translator->trans('php.date.format', array(), $config->getTransScope());
            $rtn = $value->format($format);
        }
        return $rtn;
    }

    // TODO there should be a better solution than using this... We want caching!
    public function fetchId($entity) {
        $id = null;
        $idProperty = $this->fetchIdProperty(new \ReflectionClass($entity));
        if ($idProperty) {
            $idProperty->setAccessible(TRUE);
            $id = $idProperty->getValue($entity);
        }
        return $id;
    }

    private function fetchEntityDescriptor($entity, AutoTablesConfiguration $config) {
        $reflClass = new \ReflectionClass($entity);
        $entityDescriptor = util::array_get($this->entityDescriptorMap[$reflClass->getName()]);
        if (!$entityDescriptor) {
            $entityDescriptor = $this->initDescriptors($reflClass, $entity, $config);
        }
        Ensure::ensureNotNull($entityDescriptor, 'Failed to fetch entity descriptor for [%s]', $config->getId());
        return $entityDescriptor;
    }

    private function getColumnDescriptor($entity, $columnDescriptorId, AutoTablesConfiguration $config) {
        $columnDescriptor = util::array_get($this->columnDescriptorMap[$columnDescriptorId]);
        if (!$columnDescriptor) {
            $this->initDescriptors(new \ReflectionClass($entity), $entity, $config);
            $columnDescriptor = util::array_get($this->columnDescriptorMap[$columnDescriptorId]);
            Ensure::ensureNotNull($columnDescriptor, 'Failed to load column [%s] for entity of type [%s]', $columnDescriptorId, get_class($entity));
        }
        return $columnDescriptor;
    }

    private function initDescriptors(\ReflectionClass $reflClass, $entity, AutoTablesConfiguration $config) {
        $columnDescriptors = array();
        $this->parsePropertyColumnDescriptors($reflClass, $columnDescriptors, $config);
        $this->parseMethodColumnDescriptors($reflClass, $columnDescriptors, $config);
        $entityDescriptor = new EntityDescriptor($columnDescriptors, $this->fetchIdProperty($reflClass));
        $this->entityDescriptorMap[$reflClass->getName()] = $entityDescriptor;
        return $entityDescriptor;
    }

    private function parsePropertyColumnDescriptors(\ReflectionClass $reflClass, &$columnDescriptors, AutoTablesConfiguration $config) {
        foreach ($reflClass->getProperties() as $property) {
            $column = new PropertyColumnDescriptor($property);
            $column->addORMAnnotation($this->reader->getPropertyAnnotation($property, '\Doctrine\ORM\Mapping\Column'));
            $column->addAutoTablesAnnotation($this->reader->getPropertyAnnotation($property, '\twentysteps\Bundle\AutoTablesBundle\Annotations\Column'));
            $column->addAutoTablesConfig($config, $property->getName());
            if ($column->isUsable()) {
                $column->validate();
                $this->columnDescriptorMap[$column->getId()] = $column;
                $columnDescriptors[] = $column;
            }
        }
    }

    private function parseMethodColumnDescriptors(\ReflectionClass $reflClass, &$columnDescriptors, AutoTablesConfiguration $config) {
        foreach ($reflClass->getMethods() as $method) {
            $column = null;
            $annot = $this->reader->getMethodAnnotation($method, '\twentysteps\Bundle\AutoTablesBundle\Annotations\Column');
            if ($annot) {
                $column = new MethodColumnDescriptor($method);
                Ensure::ensureTrue(count($method->getParameters()) == 0, 'Failed to use [%s] as getter method, only parameterless methods supported for @Column', $method->getName());
                Ensure::ensureTrue(StaticStringy::startsWith($method->getName(), 'get'), 'Illegal method name [%s], getter methods must start with a get prefix', $method->getName());
                $column->addAutoTablesAnnotation($annot);
                $column->addAutoTablesConfig($config, $method->getName());
            }
            if ($column && $column->isUsable()) {
                $methodName = 'set' . substr($method->getName(), 3);
                if ($reflClass->hasMethod($methodName)) {
                    $setterMethod = $reflClass->getMethod($methodName);
                    Ensure::ensureEquals(1, count($setterMethod->getParameters()), 'setter method [%s] needs to have exactly one parameter', $setterMethod->getName());
                    $column->setSetterMethod($setterMethod);
                }
                $column->validate();
                $this->columnDescriptorMap[$column->getId()] = $column;
                $columnDescriptors[] = $column;
            }
        }
    }

    private function fetchIdProperty(\ReflectionClass $reflClass) {
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
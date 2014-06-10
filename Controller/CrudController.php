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

namespace twentysteps\Bundle\AutoTablesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use twentysteps\Bundle\AutoTablesBundle\DependencyInjection\AutoTablesGlobalConfiguration;
use twentysteps\Bundle\AutoTablesBundle\Services\AutoTablesCrudService;
use twentysteps\Bundle\AutoTablesBundle\Services\RepositoryAutoTablesCrudService;
use twentysteps\Bundle\AutoTablesBundle\Util\Ensure;
use twentysteps\Bundle\AutoTablesBundle\DependencyInjection\AutoTablesConfiguration;

class CrudController extends Controller
{

    private $logger;

    public function updateAction(Request $request)
    {
        $value = $request->request->get('value');
        $id = $request->request->get('id');
        $columnId = $request->request->get('columnId');
        $columnMeta = $request->request->get('columnMeta');
        $columnDescriptorId = $columnMeta[$columnId]['columnDescriptorId'];
        $tableId = $this->fetchtableId($request);

        $this->get('logger')->info(sprintf('Update entity of type [%s] with id [%s] for column [%s] with value [%s]', $tableId, $id, $columnDescriptorId, $value));

        $config = $this->fetchAutoTablesConfiguration($tableId);
        $crudService = $this->fetchCrudService($config);
        $entity = $crudService->findEntity($id);
        Ensure::ensureNotNull($entity, 'Entity with id [%s] not found', $id);
        $entityInspector = $this->get('twentysteps_bundle.AutoTablesBundle.services.entityinspectionservice');
        $entityInspector->setValue($entity, $columnDescriptorId, $value, $config);
        $crudService->persistEntity($entity);
        return new Response($entityInspector->getValue($entity, $columnDescriptorId, $config));
    }

    public function addAction(Request $request)
    {
        $tableId = $this->fetchtableId($request);
        $config = $this->fetchAutoTablesConfiguration($tableId);
        $crudService = $this->fetchCrudService($config);
        $entityInspector = $this->get('twentysteps_bundle.AutoTablesBundle.services.entityinspectionservice');
        $entity = $crudService->createEntity();
        foreach ($request->request->keys() as $paramName) {
            if ($this->isColumnParameter($paramName)) {
                $entityInspector->setValue($entity, $paramName, $request->request->get($paramName), $config);
            }
        }
        $crudService->persistEntity($entity);
        $id = $entityInspector->fetchId($entity);

        $this->get('logger')->info(sprintf('Added new entity of type [%s] with id [%s]', $tableId, $id));

        $entityDesc = $entityInspector->parseEntity($entity);
        //$this->get('logger')->info(sprintf('Entity desc: [%s]', var_export($entityDesc, true)));

        $columns = array();
        $columns[] = $entityDesc->getId();
        foreach ($entityDesc->getColumns() as $column) {
            $columns[] = $entityInspector->getValue($entity, $column->getColumnDescriptorId(), $config);
        }

        $response = new Response(json_encode($columns));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
        //return new Response($id);
    }

    public function removeAction(Request $request)
    {
        $tableId = $this->fetchtableId($request);
        $config = $this->fetchAutoTablesConfiguration($tableId);
        $crudService = $this->fetchCrudService($config);
        $id = $request->query->get('id');
        $this->get('logger')->info(sprintf('Remove entity of type [%s] with id [%s]', $tableId, $id));
        $entity = $crudService->findEntity($id);
        $msg = 'ok';
        if ($entity) {
            $crudService->removeEntity($entity);
        } else {
            $translator = $this->get('translator');
            $msg = $translator->trans('No entity with id [%id%] found', array('%id%' => $id), $config->getTransScope());
        }
        return new Response($msg);
    }

    private function isColumnParameter($paramName)
    {
        return $paramName[0] == 'm' || $paramName[0] == 'p';
    }

    private function fetchtableId($request) {
        $tableId = $request->request->get('tableId');
        if (!$tableId) {
            $tableId = $request->query->get('tableId');
        }
        Ensure::ensureNotEmpty($tableId, 'tableId must not be empty');
        return $tableId;
    }

    /**
     * @return AutoTablesConfiguration
     */
    private function fetchAutoTablesConfiguration($tableId) {
        $options = $this->container->getParameter('twentysteps_auto_tables.config.'.$tableId);
        Ensure::ensureNotNull($options, 'Missing configuration for twentysteps_auto_tables table [%s]', $tableId);
        return new AutoTablesConfiguration($tableId, $options, new AutoTablesGlobalConfiguration($this->container->getParameter('twentysteps_auto_tables.config')));
    }

    /**
     * @return AutoTablesCrudService
     */
    private function fetchCrudService(AutoTablesConfiguration $config)
    {
        if ($config->getServiceId()) {
            $this->getLogger()->info(sprintf('Create CrudService from serviceId [%s]', $config->getServiceId()));
            $crudService = $this->get($config->getServiceId());
            Ensure::ensureNotNull($crudService, 'No service [%s] found', $crudService);
            Ensure::ensureTrue($crudService instanceof AutoTablesCrudService, 'Service [%s] has to implement %s', $config->getServiceId(), 'AutoTablesCrudService');
        } else {
            $this->getLogger()->info(sprintf('Create CrudService from repositoryId [%s]', $config->getRepositoryId()));
            $doctrine = $this->get('doctrine');
            Ensure::ensureNotEmpty($config->getRepositoryId(), 'Neither [serviceId] nor [repositoryId] defined for datatables of type [%s]', $config->getId());
            $repository = $doctrine->getRepository($config->getRepositoryId());
            Ensure::ensureNotNull($repository, 'Repository with id [%s] not found', $config->getRepositoryId());
            $crudService = new RepositoryAutoTablesCrudService($doctrine->getManager(), $repository);
        }
        return $crudService;
    }

    private function getLogger() {
        if (!$this->logger) {
            $this->logger = $this->get('logger');
        }
        return $this->logger;
    }
}

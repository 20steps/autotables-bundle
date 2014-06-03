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

namespace twentysteps\Bundle\DataTablesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use twentysteps\Bundle\DataTablesBundle\Services\DataTablesCrudService;
use twentysteps\Bundle\DataTablesBundle\Services\RepositoryDataTablesCrudService;
use twentysteps\Bundle\DataTablesBundle\Util\Ensure;
use utilphp\util;

class CrudController extends Controller
{
    public function updateAction(Request $request)
    {
        $value = $request->request->get('value');
        $id = $request->request->get('id');
        $columnId = $request->request->get('columnId');
        $columnMeta = $request->request->get('columnMeta');
        $columnDescriptorId = $columnMeta[$columnId]['columnDescriptorId'];
        $dtId = $request->request->get('dtId');

        $this->get('logger')->info(sprintf('Update entity of type [%s] with id [%s] for column [%s] with value [%s]', $dtId, $id, $columnDescriptorId, $value));

        $crudService = $this->fetchCrudService($dtId);
        $entity = $crudService->findEntity($id);
        Ensure::ensureNotNull($entity, 'Entity with id [%s] not found', $id);
        $entityInspector = $this->get('twentysteps_bundle.datatablesbundle.services.entityinspectionservice');
        $entityInspector->setValue($entity, $columnDescriptorId, $value);
        $crudService->persistEntity($entity);
        return new Response($entityInspector->getValue($entity, $columnDescriptorId));
    }

    public function addAction(Request $request)
    {
        $dtId = $this->fetchDtId($request);
        $crudService = $this->fetchCrudService($dtId);
        $entityInspector = $this->get('twentysteps_bundle.datatablesbundle.services.entityinspectionservice');
        $entity = $crudService->createEntity();
        foreach ($request->request->keys() as $paramName) {
            if ($this->isColumnParameter($paramName)) {
                $entityInspector->setValue($entity, $paramName, $request->request->get($paramName));
            }
        }
        $crudService->persistEntity($entity);
        $id = $entityInspector->fetchId($entity);

        $this->get('logger')->info(sprintf('Added new entity of type [%s] with id [%s]', $dtId, $id));

        return new Response($id);
    }

    public function removeAction(Request $request)
    {
        $dtId = $this->fetchDtId($request);
        $crudService = $this->fetchCrudService($dtId);
        $id = $request->query->get('id');
        $this->get('logger')->info(sprintf('Remove entity of type [%s] with id [%s]', $dtId, $id));
        $entity = $crudService->findEntity($id);
        $msg = 'ok';
        if ($entity) {
            $crudService->removeEntity($entity);
        } else {
            $translator = $request->get('translator');
            $msg = $translator->trans('No entity with id [%id%] found', array('%id%' => $id), $this->fetchTransScope($dtId));
        }
        return new Response($msg);
    }

    private function isColumnParameter($paramName)
    {
        return $paramName[0] == 'm' || $paramName[0] == 'p';
    }

    private function fetchDtId($request) {
        $dtId = $request->request->get('dtId');
        if (!$dtId) {
            $dtId = $request->query->get('dtId');
        }
        Ensure::ensureNotEmpty($dtId, 'dtId must not be empty');
        return $dtId;
    }

    /**
     * @return DataTablesCrudService
     */
    private function fetchCrudService($dtId)
    {
        $serviceId = util::array_get($this->container->getParameter('datatables.' . $dtId)['serviceId']);
        if ($serviceId) {
            $crudService = $this->get($serviceId);
            Ensure::ensureNotNull($crudService, 'No service [%s] found', $crudService);
            Ensure::ensureTrue($crudService instanceof DataTablesCrudService, 'Service [%s] has to implement %s', $serviceId, 'DataTablesCrudService');
        } else {
            $doctrine = $this->get('doctrine');
            $repositoryId = util::array_get($this->container->getParameter('datatables.' . $dtId)['repositoryId']);
            Ensure::ensureNotEmpty($repositoryId, 'Neither [serviceId] nor [repositoryId] defined for datatables of type [%s]', $dtId);
            $repository = $doctrine->getRepository($repositoryId);
            Ensure::ensureNotNull($repository, 'Repository with id [%s] not found', $repositoryId);
            $crudService = new RepositoryDataTablesCrudService($doctrine->getManager(), $repository);
        }
        return $crudService;
    }

    private function fetchTransScope($dtId)
    {
        return util::array_get($this->container->getParameter('datatables.' . $dtId)['transScope']) ?: 'messages';
    }
}

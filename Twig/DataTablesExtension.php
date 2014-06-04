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

namespace twentysteps\Bundle\AutoTablesBundle\Twig;

use twentysteps\Bundle\AutoTablesBundle\DependencyInjection\DataTablesConfiguration;
use twentysteps\Bundle\AutoTablesBundle\Services\EntityInspectionService;
use twentysteps\Bundle\AutoTablesBundle\Util\Ensure;
use utilphp\util;

class DataTablesExtension extends AbstractExtension
{
    private $entityInspectionService;
    private $container;

    public function __construct(EntityInspectionService $entityInspectionService, $container)
    {
        $this->entityInspectionService = $entityInspectionService;
        $this->container = $container;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('ts_dataTable_assets', array($this, 'renderAssets'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('ts_dataTable', array($this, 'renderTable'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('ts_dataTable_js', array($this, 'renderTableJs'), array('is_safe' => array('html')))
        );
    }

    /**
     * Prints a table with the given entities.
     */
    public function renderTable($args)
    {
        $config = $this->fetchDataTablesConfiguration($args);
        $array = array(
            'entities' => $this->entityInspectionService->parseEntities($this->getRequiredParameter($args, 'entities')),
            'deleteRoute' => $this->getParameter($args, 'deleteRoute', 'twentysteps_auto_tables_remove'),
            'dtId' => $config->getId(),
            'transScope' => $config->getTransScope(),
            'formAddNewRowTemplate' => $this->getParameter($args, 'formAddNewRowTemplate', 'twentystepsAutoTablesBundle:DataTablesExtension:formAddNewRow.html.twig')
        );
        return $this->render('twentystepsAutoTablesBundle:DataTablesExtension:dataTable.html.twig', $array);
    }

    /**
     * Prints the JavaScript code needed for the datatables of the given entities.
     */
    public function renderTableJs($args)
    {
        $config = $this->fetchDataTablesConfiguration($args);
        $array = array(
            'entities' => $this->entityInspectionService->parseEntities($this->getRequiredParameter($args, 'entities')),
            'updateRoute' => $this->getParameter($args, 'updateRoute', 'twentysteps_auto_tables_update'),
            'deleteRoute' => $this->getParameter($args, 'deleteRoute', 'twentysteps_auto_tables_remove'),
            'addRoute' => $this->getParameter($args, 'addRoute', 'twentysteps_auto_tables_add'),
            'dtDefaultOpts' => $this->container->getParameter('twentysteps_auto_tables.defaultDataTablesOptions'),
            'dtOpts' => $config->getDataTablesOptions(),
            'dtTagOpts' => $this->getParameter($args, 'dtOptions', array()),
            'dtId' => $config->getId(),
            'transScope' => $config->getTransScope()
        );
        return $this->render('twentystepsAutoTablesBundle:DataTablesExtension:dataTableJs.html.twig', $array);
    }

    /**
     * Renders the needed JavaScript and stylesheet includes.
     */
    public function renderAssets($args)
    {
        return $this->render('twentystepsAutoTablesBundle:DataTablesExtension:dataTableAssets.html.twig',
            array(
                'javascriptAssets' => $this->getParameter($args, 'javascriptAssets', TRUE),
                'stylesheetAssets' => $this->getParameter($args, 'stylesheetAssets', TRUE),
                'includeJquery' => $this->getParameter($args, 'includeJquery', FALSE),
                'includeJqueryUi' => $this->getParameter($args, 'includeJqueryUi', TRUE),
                'includeJqueryEditable' => $this->getParameter($args, 'includeJqueryEditable', TRUE),
                'includeJqueryDataTables' => $this->getParameter($args, 'includeJqueryDataTables', TRUE),
                'includeJqueryValidate' => $this->getParameter($args, 'includeJqueryValidate', TRUE)
            )
        );
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'datatables_extension';
    }

    /**
     * @return DataTablesConfiguration
     */
    private function fetchDataTablesConfiguration($args) {
        $dtId = $this->getRequiredParameter($args, 'dtId');
        $confKey = 'twentysteps_auto_tables.config.'.$dtId;
        Ensure::ensureTrue($this->container->hasParameter($confKey), 'Missing twentysteps_auto_tables table configuration with id [%s]', $dtId);
        $options = $this->container->getParameter($confKey);
        Ensure::ensureNotNull($options, 'Missing configuration for twentysteps_auto_tables table [%s]', $dtId);
        return new DataTablesConfiguration($dtId, $options);
    }
}
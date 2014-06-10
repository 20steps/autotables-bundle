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

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\Tests\ProjectContainer;
use Symfony\Component\HttpFoundation\RequestStack;
use twentysteps\Bundle\AutoTablesBundle\DependencyInjection\AutoTablesConfiguration;
use twentysteps\Bundle\AutoTablesBundle\DependencyInjection\AutoTablesGlobalConfiguration;
use twentysteps\Bundle\AutoTablesBundle\Services\EntityInspectionService;
use twentysteps\Bundle\AutoTablesBundle\Util\Ensure;
use utilphp\util;

class AutoTablesExtension extends AbstractExtension
{
    const JS_INCLUDE_KEY = 'tsAutoTableJsIncluded';

    private $entityInspectionService;
    private $container;
    private $requestStack;

    public function __construct(EntityInspectionService $entityInspectionService, $container, RequestStack $requestStack)
    {
        $this->entityInspectionService = $entityInspectionService;
        $this->container = $container;
        $this->requestStack = $requestStack;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('ts_auto_table', array($this, 'renderTable'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('ts_auto_table_stylesheets', array($this, 'renderStylesheets'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('ts_auto_table_js', array($this, 'renderTableJs'), array('is_safe' => array('html')))
        );
    }

    /**
     * Prints a table with the given entities.
     */
    public function renderTable($args = array())
    {
        $config = $this->fetchAutoTablesConfiguration($args);
        $array = array(
            'entities' => $this->entityInspectionService->parseEntities($this->getRequiredParameter($args, 'entities'), $config),
            'deleteRoute' => $this->getParameter($args, 'deleteRoute', 'twentysteps_auto_tables_remove'),
            'tableId' => $config->getId(),
            'transScope' => $config->getTransScope(),
            'views' => $config->getViews(),
            'frontendFramework' => $config->getFrontendFramework()
        );
        return $this->render('twentystepsAutoTablesBundle:AutoTablesExtension:autoTable.html.twig', $array);
    }

    /**
     * Prints the JavaScript code needed for the datatables of the given entities.
     */
    public function renderTableJs($args = array())
    {
        $config = $this->fetchAutoTablesConfiguration($args);
        $array = array(
            'entities' => $this->entityInspectionService->parseEntities($this->getRequiredParameter($args, 'entities'), $config),
            'updateRoute' => $this->getParameter($args, 'updateRoute', 'twentysteps_auto_tables_update'),
            'deleteRoute' => $this->getParameter($args, 'deleteRoute', 'twentysteps_auto_tables_remove'),
            'addRoute' => $this->getParameter($args, 'addRoute', 'twentysteps_auto_tables_add'),
            'dtDefaultOpts' => $this->container->getParameter('twentysteps_auto_tables.default_datatables_options'),
            'dtOpts' => $config->getDataTablesOptions(),
            'dtTagOpts' => $this->getParameter($args, 'dtOptions', array()),
            'tableId' => $config->getId(),
            'transScope' => $config->getTransScope(),
            'reloadAfterAdd' => $this->getParameter($args, 'reloadAfterAdd', 'null'),
            'includeJavascript' => $this->checkIncludeJavascript(),
            'includeJquery' => $this->getParameter($args, 'includeJquery', FALSE),
            'includeJqueryUi' => $this->getParameter($args, 'includeJqueryUi', TRUE),
            'includeJqueryEditable' => $this->getParameter($args, 'includeJqueryEditable', TRUE),
            'includeJqueryEditableDatePicker' => $this->getParameter($args, 'includeJqueryEditableDatePicker', TRUE),
            'includeJqueryDataTables' => $this->getParameter($args, 'includeJqueryDataTables', TRUE),
            'includeJqueryValidate' => $this->getParameter($args, 'includeJqueryValidate', TRUE)
        );
        return $this->render('twentystepsAutoTablesBundle:AutoTablesExtension:autoTableJs.html.twig', $array);
    }

    /**
     * Renders the needed JavaScript and stylesheet includes.
     */
    public function renderStylesheets()
    {
        return $this->render('twentystepsAutoTablesBundle:AutoTablesExtension:autoTableStylesheets.html.twig',
            array(
                'includeJqueryUi' => $this->fetchAutoTablesGlobalConfiguration()->getFrontendFramework() != FrontendFramework::BOOTSTRAP3,
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
     * @return AutoTablesConfiguration
     */
    private function fetchAutoTablesConfiguration($args) {
        $tableId = $this->getRequiredParameter($args, 'tableId');
        $confKey = 'twentysteps_auto_tables.config.'.$tableId;
        Ensure::ensureTrue($this->container->hasParameter($confKey), 'Missing twentysteps_auto_tables table configuration with id [%s]', $tableId);
        $options = $this->container->getParameter($confKey);
        Ensure::ensureNotNull($options, 'Missing configuration for twentysteps_auto_tables table [%s]', $tableId);
        $globalConf = $this->fetchAutoTablesGlobalConfiguration();
        return new AutoTablesConfiguration($tableId, $options, $globalConf);
    }

    /**
     * @return AutoTablesGlobalConfiguration
     */
    private function fetchAutoTablesGlobalConfiguration() {
        return new AutoTablesGlobalConfiguration($this->container->getParameter('twentysteps_auto_tables.config'));
    }

    /**
     * Determines whether we have to include the javascript files.
     */
    private function checkIncludeJavascript() {
        $request = $this->requestStack->getCurrentRequest();
        $rtn = !$request->attributes->has($this::JS_INCLUDE_KEY);
        if (!$rtn) {
            $request->attributes->set($this::JS_INCLUDE_KEY, true);
        }
        return $rtn;
    }
}
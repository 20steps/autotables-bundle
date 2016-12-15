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

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use twentysteps\Bundle\AutoTablesBundle\DependencyInjection\AutoTablesConfiguration;
use twentysteps\Bundle\AutoTablesBundle\DependencyInjection\AutoTablesGlobalConfiguration;
use twentysteps\Bundle\AutoTablesBundle\Services\EntityInspectionService;
use twentysteps\Commons\EnsureBundle\Ensure;
use utilphp\util;

class AutoTablesExtension extends AbstractExtension {
    const JS_INCLUDE_KEY = 'tsAutoTableJsIncluded';

    private $entityInspectionService;
    private $container;
    private $requestStack;
    private $logger;
    private $router;

    public function __construct(EntityInspectionService $entityInspectionService, RouterInterface $router, $container, RequestStack $requestStack, $logger) {
        $this->entityInspectionService = $entityInspectionService;
        $this->container = $container;
        $this->requestStack = $requestStack;
        $this->router = $router;
        $this->logger = $logger;
    }

    public function getFunctions() {
	    $options = array(
	        'is_safe' => array('html'),
	        'needs_environment' => true,
	    );
        return array(
            new \Twig_SimpleFunction('ts_auto_table_html', array($this, 'renderTable'), $options),
            new \Twig_SimpleFunction('ts_auto_table_stylesheets', array($this, 'renderStylesheets'), $options),
            new \Twig_SimpleFunction('ts_auto_table_js', array($this, 'renderTableJs'), $options),
            new \Twig_SimpleFunction('ts_auto_table_options', array($this, 'defineOptions'), $options)
        );
    }

    /**
     * Prints a table with the given entities.
     */
    public function renderTable(\Twig_Environment $env, $args = array()) {
        $array = array();
        $config = $this->fillModel($array);
        $array['deleteUrl'] = $this->fetchUrl($args, 'delete', $config->getDeleteRoute());
        $array['tableId'] = $config->getId();
        $array['transScope'] = $config->getTransScope();
        $array['views'] = $config->getViews();
        $array['frontendFramework'] = $config->getFrontendFramework();
        $array['frontendFrameworkName'] = FrontendFramework::toString($config->getFrontendFramework());
        return $this->render($env, 'twentystepsAutoTablesBundle:AutoTablesExtension:autoTable.html.twig', $array);
    }

    /**
     * Prints the JavaScript code needed for the datatables of the given entities.
     */
    public function renderTableJs(\Twig_Environment $env, $args = array()) {
        $array = array();
        $config = $this->fillModel($array);
        $array['updateUrl'] =  $this->fetchUrl($args, 'update', $config->getUpdateRoute());
        $array['deleteUrl'] = $this->fetchUrl($args, 'delete', $config->getDeleteRoute());
        $array['addUrl'] = $this->fetchUrl($args, 'add', $config->getAddRoute());
        $array['dtDefaultOpts'] = $this->container->getParameter('twentysteps_auto_tables.default_datatables_options');
        $array['dtOpts'] = $config->getDataTablesOptions();
        $array['dtTagOpts'] = $this->getParameter($args, 'dtOptions', array());
        $array['tableId'] = $config->getId();
        $array['transScope'] = $config->getTransScope();
        $array['reloadAfterAdd'] = $this->getParameter($args, 'reloadAfterAdd', true);
        $array['reloadAfterUpdate'] = $this->getParameter($args, 'reloadAfterUpdate', false);
        $array['includeJavascript'] = $this->checkIncludeJavascript();
        $array['includeBootstrap3'] = $this->getParameter($args, 'includeBootstrap3', false);
        $array['includeJquery'] = $this->getParameter($args, 'includeJquery', FALSE);
        $array['includeJqueryUi'] = $this->getParameter($args, 'includeJqueryUi', $config->getFrontendFramework() == FrontendFramework::JQUERY_UI);
        $array['includeJqueryEditable'] = $this->getParameter($args, 'includeJqueryEditable', TRUE);
        $array['includeJqueryEditableDatePicker'] = $this->getParameter($args, 'includeJqueryEditableDatePicker', $config->getFrontendFramework() == FrontendFramework::JQUERY_UI);
        $array['includeJqueryEditableBootstrapDatePicker'] = $this->getParameter($args, 'includeJqueryEditableBootstrapDatePicker', $config->getFrontendFramework() == FrontendFramework::BOOTSTRAP3);
        $array['includeJqueryDataTables'] = $this->getParameter($args, 'includeJqueryDataTables', TRUE);
        $array['includeJqueryValidate'] = $this->getParameter($args, 'includeJqueryValidate', TRUE);
        $array['useJqueryUi'] = $config->getFrontendFramework() == FrontendFramework::JQUERY_UI;
        $array['useBootstrap'] = $config->getFrontendFramework() == FrontendFramework::BOOTSTRAP3;
        return $this->render($env, 'twentystepsAutoTablesBundle:AutoTablesExtension:autoTableJs.html.twig', $array);
    }

    /**
     * Renders the needed JavaScript and stylesheet includes.
     */
    public function renderStylesheets(\Twig_Environment $env, $args = array()) {
        $frontendFramework = $this->fetchAutoTablesGlobalConfiguration()->getFrontendFramework();
        return $this->render($env, 'twentystepsAutoTablesBundle:AutoTablesExtension:autoTableStylesheets.html.twig',
            array(
                'includeJqueryUi' => $this->getParameter($args, 'includeJqueryUi', $frontendFramework == FrontendFramework::JQUERY_UI),
                'includeBootstrap3' => $this->getParameter($args, 'includeBootstrap3', false)
            )
        );
    }

    /**
     * Define options to be used for "ts_auto_table" and "ts_auto_table_js".
     */
    public function defineOptions(\Twig_Environment $env, $args) {
        $request = $this->requestStack->getCurrentRequest();
        if ($request) {
            $config = $this->fetchAutoTablesConfiguration($args);
            $entities = $this->entityInspectionService->parseEntities($this->getParameter($args, 'entities', array()), $config);
            $request->attributes->set('tsAutoTablesConfig', $config);
            $request->attributes->set('tsAutoTablesEntities', $entities);
            $request->attributes->set('tsAutoTablesEntityDescriptor', $this->entityInspectionService->fetchEntityDescriptor($config));
        }
        return '';
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName() {
        return 'datatables_extension';
    }

    /**
     * @return AutoTablesConfiguration
     */
    private function fetchAutoTablesConfiguration($args) {
        $tableId = $this->getRequiredParameter($args, 'tableId');
        $confKey = 'twentysteps_auto_tables.config.' . $tableId;
        Ensure::isTrue($this->container->hasParameter($confKey), 'Missing twentysteps_auto_tables table configuration with id [%s]', $tableId);
        $options = $this->container->getParameter($confKey);
        Ensure::isNotNull($options, 'Missing configuration for twentysteps_auto_tables table [%s]', $tableId);
        $globalConf = $this->fetchAutoTablesGlobalConfiguration();
        return $this->mergeColumnsConfiguration(new AutoTablesConfiguration($tableId, $options, $globalConf), $args);
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

    private function mergeColumnsConfiguration(AutoTablesConfiguration $config, $args) {
        $newColArgs = util::array_get($args['columns'], array());
        foreach ($newColArgs as $newColArg) {
            $selector = $newColArg['selector'];
            Ensure::isNotEmpty($selector, 'Missing selector in column configuration');
            $colArg = util::array_get($config->getColumns()[$selector], null);
            if ($colArg) {
                // overwrite the settings
                $config->putColumn($selector, array_merge($colArg, $newColArg));
            } else {
                // define a new entry
                $config->putColumn($selector, $newColArg);
            }
        }
        return $config;
    }

    /**
     * @return AutoTablesConfiguration
     */
    private function fillModel(&$array) {
        $config = null;
        $entityDescriptor = null;
        $request = $this->requestStack->getCurrentRequest();
        if ($request) {
            $config = $request->get('tsAutoTablesConfig');
            $entityDescriptor = $request->get('tsAutoTablesEntityDescriptor');
            $array['entities'] = $request->get('tsAutoTablesEntities');
            $array['entityDescriptor'] = $entityDescriptor;
        }
        Ensure::isNotNull($config, 'Missing config, did you forget to use ts_auto_table_options?');
        Ensure::isNotNull($entityDescriptor, 'Missing entityDescriptor, did you forget to use ts_auto_table_options?');
        return $config;
    }

    private function fetchUrl($args, $prefix, $defaultRoute) {
        $url = $this->getParameter($args, $prefix.'Url', null);
        return $url !== null ? $url : $this->router->generate($this->getParameter($args, $prefix.'Route', $defaultRoute));
    }
}
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

namespace twentysteps\Bundle\DataTablesBundle\Twig;

use twentysteps\Bundle\DataTablesBundle\Services\EntityInspectionService;
use twentysteps\Bundle\DataTablesBundle\Util\Ensure;
use utilphp\util;

class DTBExtension extends AbstractExtension
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
            // TODO switch to:
            // new \Twig_SimpleFunction('code', array($this, 'getCode'), array('is_safe' => array('html'))),
            'ts_dt_jsIncludes' => new \Twig_Function_Method($this, 'renderJsIncludes', array('is_safe' => array('html'))),
            'ts_dt_stylesheetIncludes' => new \Twig_Function_Method($this, 'renderStylesheetIncludes', array('is_safe' => array('html'))),
            'ts_dt_render_table' => new \Twig_Function_Method($this, 'renderTable', array('is_safe' => array('html'))),
            'ts_dt_render_table_js' => new \Twig_Function_Method($this, 'renderTableJs', array('is_safe' => array('html')))
        );
    }

    /**
     * Prints a table with the given entities.
     */
    public function renderTable($args)
    {
        $dtId = $this->getDtId($args);
        return $this->render('twentystepsDataTablesBundle:DTBExtension:renderTable.html.twig',
            array(
                'entities' => $this->entityInspectionService->parseEntities($args['entities']),
                'dtId' => $dtId
            )
        );
    }

    /**
     * Prints the JavaScript code needed for the datatables of the given entities.
     */
    public function renderTableJs($args)
    {
        $dtId = $this->getDtId($args);
        return $this->render('twentystepsDataTablesBundle:DTBExtension:renderTableJs.html.twig',
            array(
                'entities' => $this->entityInspectionService->parseEntities($args['entities']),
                'dtId' => $dtId,
                'updateRoute' => util::array_get($args['updateRoute']) ? : 'twentysteps_data_tables_update',
                'deleteRoute' => util::array_get($args['deleteRoute']) ? : 'twentysteps_data_tables_remove',
                'addRoute' => util::array_get($args['addRoute']) ? : 'twentysteps_data_tables_add',
                'dtDefaultOpts' => $this->getDefaultOptions($dtId),
                'dtOpts' => util::array_get($args['dtOptions']) ? : array()
            )
        );
    }

    /**
     * Renders the needed includes for the JavaScript files.
     */
    public function renderJsIncludes($includeJquery = false)
    {
        return $this->render('twentystepsDataTablesBundle:DTBExtension:renderJsIncludes.html.twig',
            array(
                'includeJquery' => $includeJquery
            )
        );
    }

    /**
     * Renders the needed includes for the stylesheet files.
     */
    public function renderStylesheetIncludes()
    {
        return $this->render('twentystepsDataTablesBundle:DTBExtension:renderStylesheetIncludes.html.twig',
            array()
        );
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'dtb_extension';
    }

    /**
     * Retrieves the default options for the given key.
     */
    private function getDefaultOptions($dtKey)
    {
        return util::array_get($this->container->getParameter('datatables.' . $dtKey)['defaultOptions']) ? : array();
    }

    private function getDtId($args)
    {
        $dtId = util::array_get($args['dtId']);
        Ensure::ensureNotEmpty($dtId, "dtId must not be empty");
        return $dtId;
    }
}
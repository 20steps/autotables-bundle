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

namespace twentysteps\Bundle\AutoTablesBundle\DependencyInjection;

use twentysteps\Bundle\AutoTablesBundle\Twig\FrontendFramework;
use utilphp\util;

/**
 * Handle for accessing a specific DataTables config entry.
 */
class AutoTablesConfiguration {

    private $id;
    private $serviceId;
    private $repositoryId;
    private $transScope;
    private $dataTablesOptions;
    private $views;
    private $frontendFramework;
    private $columns;

    public function __construct($id, $args, AutoTablesGlobalConfiguration $globalConf) {
        $this->id = $id;
        $this->serviceId = util::array_get($args['service'], null);
        $this->repositoryId = util::array_get($args['repository'], null);
        $this->transScope = util::array_get($args['trans_scope'], $globalConf->getTransScope());
        $this->dataTablesOptions = util::array_get($args['datatables_options'], $globalConf->getDataTablesOptions());
        $this->views = util::array_get($args['views'], '');
        $this->frontendFramework = $globalConf->getFrontendFramework();
        $this->columns = util::array_get($args['columns'], array());
    }

    /**
     * @return string
     */
    public function getDataTablesOptions() {
        return $this->dataTablesOptions;
    }

    /**
     * @return string
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getRepositoryId() {
        return $this->repositoryId;
    }

    /**
     * @return string
     */
    public function getServiceId() {
        return $this->serviceId;
    }

    /**
     * @return string
     */
    public function getTransScope() {
        return $this->transScope;
    }

    /**
     * @return null
     */
    public function getViews() {
        return $this->views;
    }

    public function getFrontendFramework() {
        return $this->frontendFramework;
    }

    /**
     * @return mixed
     */
    public function getColumns() {
        return $this->columns;
    }

    public function putColumn($key, $column) {
        $this->columns[$key] = $column;
    }
}
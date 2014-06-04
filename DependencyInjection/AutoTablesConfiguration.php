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

use twentysteps\Bundle\AutoTablesBundle\Util\Ensure;
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

    public function __construct($id, $args) {
        $this->id = $id;
        $this->serviceId = $this->getOption($args, 'service_id');
        $this->repositoryId = $this->getOption($args, 'repository_id');
        $this->transScope = $this->getOption($args, 'trans_scope', 'messages');
        $this->dataTablesOptions = $this->getOption($args, 'datatables_options', '{}');
    }

    /**
     * @return string
     */
    public function getDataTablesOptions()
    {
        return $this->dataTablesOptions;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getRepositoryId()
    {
        return $this->repositoryId;
    }

    /**
     * @return string
     */
    public function getServiceId()
    {
        return $this->serviceId;
    }

    /**
     * @return string
     */
    public function getTransScope()
    {
        return $this->transScope;
    }

    private function getOption($args, $key, $defaultValue = NULL) {
        return util::array_get($args[$key]) ?: $defaultValue;
    }
}
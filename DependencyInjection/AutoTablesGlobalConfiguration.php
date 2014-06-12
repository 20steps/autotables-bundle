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
use twentysteps\Bundle\AutoTablesBundle\Util\Ensure;
use utilphp\util;

/**
 * Global configuration options for autotables.
 */
class AutoTablesGlobalConfiguration {

    private $transScope;
    private $dataTablesOptions;
    private $frontendFramework;

    public function __construct($args) {
        $this->transScope = util::array_get($args['trans_scope'], 'messages');
        $this->dataTablesOptions = util::array_get($args['datatables_options'], '{}');
        $this->frontendFramework = FrontendFramework::fromString(util::array_get($args['frontend_framework'], FrontendFramework::toString(FrontendFramework::JQUERY_UI)));
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
    public function getTransScope()
    {
        return $this->transScope;
    }

    public function getFrontendFramework()
    {
        return $this->frontendFramework;
    }
}
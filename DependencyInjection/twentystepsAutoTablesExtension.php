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

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use twentysteps\Commons\EnsureBundle\Ensure;
use utilphp\util;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class twentystepsAutoTablesExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $tables = util::array_get($config['tables']);
        if ($tables) {
            foreach($tables as $tableDef) {
                $id = util::array_get($tableDef['id']);
                Ensure::isNotEmpty($id, "Missing [id] option in twentysteps_auto_tables table definition");
                $json = util::array_get($tableDef['dataTablesOptions']) ?: '{}';
                Ensure::isTrue($this->isValidJson($json), 'Encountered illegal JSON for twentysteps_auto_tables table with id [%s] in config: %s', $id,  $json);
                $container->setParameter('twentysteps_auto_tables.config.'.$id, $tableDef);
            }
        }

        $defaultOpts = util::array_get($config['default_datatables_options']) ?: '{}';
        Ensure::isTrue($this->isValidJson($defaultOpts), 'Encountered illegal JSON in config: %s', $defaultOpts);
        $container->setParameter('twentysteps_auto_tables.default_datatables_options', $defaultOpts);

        $container->setParameter('twentysteps_auto_tables.config', $config);
    }

    private function isValidJson($json) {
        return !is_null(json_decode($json));
    }
}

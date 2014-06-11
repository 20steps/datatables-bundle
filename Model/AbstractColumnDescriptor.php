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

namespace twentysteps\Bundle\AutoTablesBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use twentysteps\Bundle\AutoTablesBundle\Annotations as AUT;
use twentysteps\Bundle\AutoTablesBundle\DependencyInjection\AutoTablesConfiguration;
use twentysteps\Bundle\AutoTablesBundle\Util\Ensure;
use utilphp\util;

abstract class AbstractColumnDescriptor {

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $type;

    /**
     * @var int
     */
    private $order = 10000;

    /**
     * @var boolean
     */
    private $readOnly = false;

    /**
     * @var boolean
     */
    private $ignore = false;

    /**
     * @var InitializerInfo
     */
    private $initializer;

    protected function __construct($id, $name) {
        $this->id = $id;
        $this->name = $name;
    }

    public function addORMAnnotation(ORM\Column $column = null) {
        if ($column) {
            $this->name = $column->name ? : $this->name;
            $this->type = $column->type ? : $this->type;
        }
    }

    public function addAutoTablesAnnotation(AUT\Column $column = null) {
        if ($column) {
            $this->name = $column->getName() ? : $this->name;
            $this->type = $column->getType() ? : $this->type;
            $this->order = $column->getOrder();
            $this->readOnly = $column->isReadOnly();
            $this->ignore = $column->isIgnore();
            if ($column->getInitializer()) {
                if (!$this->initializer) {
                    $this->initializer = new InitializerInfo();
                }
                $this->initializer->addInitializerAnnotation($column->getInitializer());
            }
        }
    }

    public function addAutoTablesConfig(AutoTablesConfiguration $config, $selector) {
        $columnOverwrite = util::array_get($config->getColumns()[$selector]);
        if ($columnOverwrite) {
            $this->readOnly = util::array_get($columnOverwrite['readOnly'], $this->readOnly);
            $this->name = util::array_get($columnOverwrite['name'], $this->name);
            $this->type = util::array_get($columnOverwrite['type'], $this->type);
            $this->order = util::array_get($columnOverwrite['order'], $this->order);
            $this->ignore = util::array_get($columnOverwrite['ignore'], $this->ignore);
        }
    }

    /**
     * Returns true, if the column should be used in auto generated tables.
     */
    public function isUsable() {
        return !$this->ignore && $this->name && $this->type;
    }

    /**
     * Throws an exception if the settings are inconsistent.
     */
    public function validate() {
        if ($this->initializer) {
            Ensure::ensureFalse($this->initializer->getRepository() && $this->initializer->getValue(), 'It makes no sense to define initializer repository and value simultaneously for column [%s]', $this->name);
        }
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
    public function getName() {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getOrder() {
        return $this->order;
    }

    /**
     * @return string
     */
    public function getType() {
        return $this->type;
    }

    /**
     * @return boolean
     */
    public function isReadOnly() {
        return $this->readOnly;
    }

    /**
     * @return \twentysteps\Bundle\AutoTablesBundle\Model\InitializerInfo
     */
    public function getInitializer() {
        return $this->initializer;
    }

    public abstract function getValue($entity);

    public abstract function setValue($entity, $value);
}
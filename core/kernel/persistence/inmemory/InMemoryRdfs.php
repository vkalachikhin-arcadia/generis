<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Konstantin Sasim <sasim@1pt.com>
 * @license GPLv2
 * @package tao
 *
 */

namespace oat\generis\model\kernel\persistence\inmemory;

use oat\generis\model\data\RdfsInterface;

class InMemoryRdfs implements RdfsInterface {

    /**
     * @var InMemoryModel
     */
    private $model;

    public function __construct(InMemoryModel $model) {
        $this->model = $model;
    }
    /**
     * Returns the implementation of the class interface
     *
     * @return \core_kernel_persistence_ClassInterface
     */
    public function getClassImplementation()
    {
        return new StubPersistenceClass($this->model);
    }

    /**
     * Returns the implementation of the resource interface
     *
     * @return \core_kernel_persistence_ResourceInterface
     */
    public function getResourceImplementation()
    {
        return new StubPersistenceResource($this->model);
    }

    /**
     * Returns the implementation of the property interface
     *
     * @return \core_kernel_persistence_PropertyInterface
     */
    public function getPropertyImplementation()
    {
        return new StubPersistenceProperty($this->model);
    }
}
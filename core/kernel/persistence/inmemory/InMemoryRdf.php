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

use oat\generis\model\data\RdfInterface;
use Traversable;

class InMemoryRdf implements RdfInterface {

    /** @var InMemoryModel */
    protected $model;

    protected function getData()
    {
        return $this->model->getData();
    }

    public function __construct(InMemoryModel $model)
    {
        $this->model = $model;
    }
    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     */
    public function getIterator()
    {
        return new StubPersistenceIterator($this->getData());
    }

    /**
     * Returns an array of the triples with the given subject, predicate
     *
     * @param string $subject
     * @param string $predicate
     * @return array
     */
    public function get($subject, $predicate)
    {
        throw new \common_Exception('Not implemented');
    }

    /**
     * Adds a triple to the model
     *
     * @param \core_kernel_classes_Triple $triple
     */
    public function add(\core_kernel_classes_Triple $triple)
    {
        $this->model->add($triple);
        return 1;
    }

    /**
     * Removes the triple
     *
     * @param \core_kernel_classes_Triple $triple
     */
    public function remove(\core_kernel_classes_Triple $triple)
    {
        $this->model->remove($triple);
        return 1;
    }

    /**
     * Returns an array of the triples with the given predicate, object
     *
     * @param string $predicate
     * @param string $object
     * @return array
     */
    public function search($predicate, $object)
    {
        throw new \common_Exception('Not implemented');
    }

}
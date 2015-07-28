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

use oat\generis\model\data\Model;
use oat\oatbox\Configurable;

use oat\generis\model\kernel\persistence\inmemory\query;

class InMemoryModel extends Configurable
    implements Model {

    const OPTION_NEW_TRIPLE_MODEL = 'addTo';

    protected $data = array();
    /**
     * Experimental interface to access the data of the model
     * Will throw an exception on all current implementations
     *
     * @return \oat\generis\model\data\RdfInterface
     */
    function getRdfInterface()
    {
        return new InMemoryRdf($this);
    }

    /**
     * Expected interface to access the data of the model
     *
     * @return \oat\generis\model\data\RdfsInterface
     */
    function getRdfsInterface()
    {
        return new InMemoryRdfs($this);
    }

    function getData()
    {
        return $this->data;
    }

    protected function getTripleKey(\core_kernel_classes_Triple $triple)
    {
        return serialize(
            array($triple->subject, $triple->predicate, $triple->object, is_null($triple->lg) ? '' : $triple->lg, $triple->modelid)
        );
    }

    function add(\core_kernel_classes_Triple $triple)
    {
        $this->data[$this->getTripleKey($triple)] = $triple;
    }

    function remove(\core_kernel_classes_Triple $triple)
    {
        $key = $this->getTripleKey($triple);

        if( isset($this->data[$key]) ){
            unset($this->data[$key]);
        }
    }

    public function getNewTripleModelId()
    {
        return $this->getOption(self::OPTION_NEW_TRIPLE_MODEL);
    }

    public function getWhere(array $where, $like = false )
    {
        $returnValue = array();
        $queryWhere = new query\QueryWhere($where, $like);

        foreach($this->data as $triple){
            if( $queryWhere->testTriple($triple) === true ){
                $returnValue[] = $triple;
            }
        }

        return $returnValue;
    }

    public function removeWhere(array $where)
    {
        $deletedCount = 0;
        $queryWhere = new query\QueryWhere($where, false);

        foreach($this->data as $key => $triple){
            if( $queryWhere->testTriple($triple) === true ){
                unset($this->data[$key]);
                $deletedCount++;
            }
        }

        return $deletedCount;
    }

    public function search($classUri, $propertyFilters, $and = true, $like = true, $lang = '', $offset = 0, $limit = 0, $order = '', $orderDir = 'ASC')
    {
        $found = false;
        $result = $this->getWhere(array(
            'predicate' => 'RDF_TYPE',
            'object'    => $classUri
        ));

        if( count($result) ){
            $found = true;

            foreach ($propertyFilters as $propertyUri => $filterValues) {
                $filterResult = $result = $this->getWhere(array(
                    'predicate' => $propertyUri,
                    'object'    => $filterValues,
                ),
                    $like
                );

                if( count($filterResult) === 0 ){
                    $found = false;
                    break;
                }

                $result = array_merge($result, $filterResult);
            }
        }


        if( $found ){
            $filter = new query\QueryResultsFilter($this);

            $result = $filter->forResult($result)
                ->lang($lang)
                ->order($order, $orderDir)
                ->limit($limit)
                ->offset($offset)
                ->distinct()
                ->get();
        } else {
            $result = array();
        }

        return $result;
    }

}
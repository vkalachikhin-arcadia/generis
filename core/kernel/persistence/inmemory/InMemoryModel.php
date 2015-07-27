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

    protected function prepareWhere(array $where)
    {
        $validFields = array();
        $tripleRef = new \ReflectionClass(new \core_kernel_classes_Triple());

        foreach($tripleRef->getProperties() as $property){
            $validFields[] = $property;
        }

        foreach ($where as $key => $condition) {
            if (!in_array($key, $validFields)) {
                unset($where[$key]);
            }
        }

        return $where;
    }

    protected function testTriple(\core_kernel_classes_Triple $triple, array $where, $like = false)
    {
        $returnValue = true;

        foreach( $where as $field => $condition ){
            $accepted = false;
            $condition = is_array($condition) ? $condition : array($condition);

            foreach($condition as $part ){
                if( $part instanceof \core_kernel_classes_Resource ){
                    $part = $part->getUri();
                    $like = false;//uri liking makes no sense
                }

                $accepted = ( $like === false ) ? ($triple->$field == $condition) : preg_match($this->prepareLike($part), $triple->$field);

                if( $accepted ){
                    break;
                }
            }

            if( !$accepted ) {
                $returnValue = false;
                break;
            }
        }

        return $returnValue;
    }

    protected function prepareLike($likeStr){

        $wildcard = mb_strpos($likeStr, '*', 0, 'UTF-8') !== false;
        $likeStr = trim(str_replace('*', '%', $likeStr));

        if (!$wildcard && !preg_match("/^%/", $likeStr)) {
            $likeStr = "%" . $likeStr;
        }
        if (!$wildcard && !preg_match("/%$/", $likeStr)) {
            $likeStr = $likeStr . "%";
        }
        if (!$wildcard && $likeStr === '%') {
            $likeStr = '%%';
        }
        //now treated to be valid LIKE

        $likeStr = str_replace('%', '.*?', $likeStr);
        $likeStr = str_replace('_', '.', $likeStr);

        $pattern = '/'.$likeStr.'/i';
        return $pattern;
    }

    protected function distinct(array $triples, array $by = array('subject') ){
        $returnValue = array();

        foreach ($triples as $key => $triple) {
            $hashBase = array();
            foreach ($by as $field) {
                $hashBase[] = $triple->$field;
            }
            $hash = md5(serialize($hashBase));

            if( !array_key_exists($hash, $returnValue) ){
                $returnValue[$hash] = $triple;
            }
        }

        return $returnValue;
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

    public function search($classUri, $propertyFilters, $and = true, $like = true, $lang = '', $offset = 0, $limit = 0, $order = '', $orderDir = 'ASC')
    {
        $found = false;
        $result = $this->getWhere(array(
            'predicate' => 'RDF_TYPE',
            'object'    => $classUri
        ));

        if( count($result) > 0 ){
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

        if( $found === true ){
            $result = $this->distinct($result);


        }



    }

    protected function langAndOrder(array $triples, $lang = '', $order = '', $orderDir = 'ASC')
    {
        $doOrder = false;
        $returnValue = array();

        $where = array(
            'subject' => array(),
            'lg' => array('')
        );

        if( $lang !== '' ){
            $where['lg'][] = $lang;
        }

        if( $order !== '' ){
            $doOrder = true;
            $where['predicate'] = $order;
        }

        foreach ($triples as $triple) {
            $where['subject'][] = $triple->subject;
        }

        $result = $this->getWhere($where);

        if( $doOrder === true ){
            foreach( $result as $triple ){
                $returnValue[ (string) $triple->object ] = $triple;
            }
            ksort($returnValue);

            if( $orderDir === 'DESC' ){
                $returnValue = array_reverse($returnValue);
            }
        } else {
            $returnValue = $result;
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

}
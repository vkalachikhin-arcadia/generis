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

namespace oat\generis\model\kernel\persistence\inmemory\query;

use oat\generis\model\kernel\persistence\inmemory\InMemoryModel;

class QueryResultsFilter
{
    const ORDER_ASC  = 'ASC';
    const ORDER_DESC = 'DESC';

    /** @var  InMemoryModel */
    protected $model;
    protected $isDistinct = false;
    protected $doOrder    = false;

    protected $limit  = 0;
    protected $offset = 0;

    protected $filter = array('lg' => array(''));

    protected $orderDir = 'ASC';

    public function __construct(InMemoryModel $model)
    {
        $this->model = $model;
    }

    public function forResult(array $for)
    {
        $this->filter['subject'] = array();

        foreach ($for as $triple) {
            $this->filter['subject'] = $triple->subject;
        }

        return $this;
    }

    public function distinct()
    {
        $this->isDistinct = true;
        return $this;
    }

    public function lang( $lang )
    {
        if( $lang !== '' ){
            $this->filter['lg'][] = $lang;
        }

        return $this;
    }

    public function order($by, $direction = self::ORDER_ASC)
    {
        $this->doOrder = true;

        if( in_array($direction, array(self::ORDER_ASC, self::ORDER_DESC))){
            $this->orderDir = $direction;
        }

        if( $by instanceof \core_kernel_classes_Resource ){
            $this->filter['predicate'] = $by->getUri();
        } else {
            $this->filter['predicate'] = $by;
        }

        return $this;
    }

    public function offset($offset)
    {
        $this->offset = intval($offset);

        return $this;
    }

    public function limit($limit)
    {
        $this->limit = intval($limit);

        return $this;
    }

    public function get()
    {
        $returnValue = array();

        if( $this->isDistinct === true ){
            $this->filter['subject']= array_unique($this->filter['subject']);
        }

        $result = $this->model->getWhere($this->filter);

        if( $this->doOrder === true ){
            foreach( $result as $triple ){
                $returnValue[ (string) $triple->object ] = $triple;
            }
            ksort($returnValue);

            if( $this->orderDir === self::ORDER_DESC ){
                $returnValue = array_reverse($returnValue);
            }
        } else {
            $returnValue = $result;
        }

        return array_slice($returnValue, $this->offset, $this->limit);
    }
}

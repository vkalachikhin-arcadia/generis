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


class QueryWhere
{
    protected $like     = false;
    protected $where    = array();
    protected $prepared = array();

    public function __construct($where, $like = false)
    {
        $this->where = $where;
        $this->like  = $like;

        $this->filterFields();
        $this->prepare();
    }

    protected function filterFields()
    {
        $validFields = array();
        $tripleRef = new \ReflectionClass(new \core_kernel_classes_Triple());

        foreach($tripleRef->getProperties() as $property){
            $validFields[] = $property;
        }

        foreach ($this->where as $key => $condition) {
            if (in_array($key, $validFields)) {
                unset($this->where[$key]);
            }
        }
    }

    protected function prepare()
    {
        $this->prepared = array();


        foreach ($this->where as $key => $condition) {
            $this->prepared[$key] = array();
            $condition = is_array($condition) ? $condition : array($condition);

            foreach($condition as $part ) {
                $like = $this->like;
                if ($part instanceof \core_kernel_classes_Resource) {
                    $part = $part->getUri();
                    $like = false;//uri liking makes no sense
                }

                $this->prepared[$key] = ($like === false) ? new SimpleQueryCondition($part) : new LikeQueryCondition($condition);
            }
        }
    }

    /**
     * @param \core_kernel_classes_Triple $triple
     * @return bool
     */
    public function testTriple(\core_kernel_classes_Triple $triple)
    {
        $returnValue = true;

        foreach( $this->where as $field => $conditionCollection ){
            $accepted = false;

            /** @var AbstractQueryCondition $condition */
            foreach($conditionCollection as $condition ) {
                $accepted = $condition->evaluate($triple->$field);
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
}

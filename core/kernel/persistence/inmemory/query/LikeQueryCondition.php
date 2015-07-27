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


class LikeQueryCondition extends AbstractQueryCondition
{
    public function __construct($condition)
    {
        parent::__construct($condition);

        $this->prepareCondition();
    }

    protected function prepareCondition()
    {
        $wildcard = mb_strpos($this->condition, '*', 0, 'UTF-8') !== false;
        $this->condition = trim(str_replace('*', '%', $this->condition));

        if (!$wildcard && !preg_match("/^%/", $this->condition)) {
            $this->condition = "%" . $this->condition;
        }
        if (!$wildcard && !preg_match("/%$/", $this->condition)) {
            $this->condition .= "%";
        }
        if (!$wildcard && $this->condition === '%') {
            $this->condition = '%%';
        }
        //now treated to be valid LIKE

        $this->condition = str_replace('%', '.*?', $this->condition);
        $this->condition = str_replace('_', '.', $this->condition);

        $this->condition = '/'.$this->condition.'/i';
    }

    /**
     * @param $value
     * @return mixed
     */
    function evaluate($value)
    {
        return (preg_match($this->condition, $value) === 1);
    }
}

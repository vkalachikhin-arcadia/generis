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
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 *
 * Content type accepted can't be satisfied
 * @access public
 * @author Gyula Szucs, <gyula@taotesting.com>
 * @package generis
 
 */
class common_exception_MethodNotAllowed extends common_exception_BadRequest
{
    /**
     * @var string[]|null
     */
    protected $allowedMethods;

    /**
     * @param string|null $message
     * @param int $code
     * @param string[]|null $allowedMethods
     */
    public function __construct($message = null, $code = 0, array $allowedMethods = null)
    {
        parent::__construct($message, $code);
        $this->allowedMethods = $allowedMethods;
    }

    /**
     * @return string[]|null
     */
    public function getAllowedMethods()
    {
        return $this->allowedMethods;
    }

    public function getUserMessage()
    {
        return __("Request method is not allowed.");
    }
}

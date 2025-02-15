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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA;
 */

namespace oat\oatbox\service;

use common_Utils as Utils;
use InvalidArgumentException;
use oat\oatbox\PhpSerializable;

class EnvironmentVariable implements PhpSerializable
{
    /** @var string */
    private $name;

    public function __construct($name)
    {
        if(!is_string($name)) {
            throw new InvalidArgumentException('Environment variable name must be a string.');
        }

        $this->name = $name;
    }

    public function __toPhpCode()
    {
        return 'new ' . __CLASS__ . '(' . Utils::toPHPVariableString($this->name) . ')';
    }

    public function __toString()
    {
        return (string) $_ENV[$this->name];
    }
}

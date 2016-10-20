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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA;
 *
 */
namespace oat\oatbox\service;

use oat\oatbox\Configurable;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

/**
 * Configurable base service
 *
 * inspired by Solarium\Core\Configurable by Bas de Nooijer
 * https://github.com/basdenooijer/solarium/blob/master/library/Solarium/Core/Configurable.php
 *
 * @author Joel Bout <joel@taotesting.com>
 */
abstract class ConfigurableService extends Configurable implements ServiceLocatorAwareInterface
{

    private $subServices = [];

    use ServiceLocatorAwareTrait;

    public function setServiceManager(ServiceManager $serviceManager)
    {
        return $this->setServiceLocator($serviceManager);
    }

    /**
     *
     * @return \oat\oatbox\service\ServiceManager
     */
    public function getServiceManager()
    {
        return $this->getServiceLocator();
    }

    /**
     * Get a subservice from the current service
     *
     * @param unknown $id
     * @param string $interface
     * @throws ServiceNotFoundException
     * @return multitype:
     */
    public function getSubService($id, $interface = null)
    {
        if (! isset($this->subServices[$id])) {
            if ($this->hasOption($id)) {
                $service = $this->buildService($this->getOption($id));
                $this->subServices[$id] = $service;
            } else {
                throw new ServiceNotFoundException($id);
            }
        }
        return $this->subServices[$id];
    }

    private function buildService($serviceOption)
    {
        if ($serviceOption instanceof ConfigurableService) {
            if (is_null($interface) || is_a($serviceOption, $interface)) {
                $this->getServiceManager()->propagate($serviceOption);
                return $serviceOption;
            } else {
                throw new InvalidService('Service must implements ' . $this->subServiceInterface);
            }
        } elseif (is_array($serviceOption) && isset($serviceOption['class'])) {
            $classname = $serviceOption['class'];
            $options = isset($serviceOption['options']) ? $serviceOption['options'] : [];
            if (is_null($interface) || is_a($classname, $interface, true)) {
                $serviceInstance = $this->getServiceManager()->build($classname, $options);
                return $serviceInstance;
            } else {
                throw new InvalidService('Service must implements ' . $this->subServiceInterface);
            }
        }
    }
}
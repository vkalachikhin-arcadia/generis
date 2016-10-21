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
use oat\oatbox\service\exception\InvalidService;
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
     * @param $id
     * @param string $interface
     * @throws ServiceNotFoundException
     * @return object
     */
    public function getSubService($id, $interface = null)
    {
        if (! isset($this->subServices[$id])) {
            if ($this->hasOption($id)) {
                $service = $this->buildService($this->getOption($id), $interface);
                if ($service) {
                    $this->subServices[$id] = $service;
                } else {
                    throw new ServiceNotFoundException($id);
                }
            } else {
                throw new ServiceNotFoundException($id);
            }
        }
        return $this->subServices[$id];
    }

    /**
     * @param      $serviceOption
     * @param null $interface
     *
     * @return null|object
     * @throws InvalidService
     */
    private function buildService($serviceOption, $interface = null)
    {
        if ($serviceOption instanceof ConfigurableService) {
            if (is_null($interface) || is_a($serviceOption, $interface)) {
                $this->getServiceManager()->propagate($serviceOption);
                return $serviceOption;
            } else {
                throw new InvalidService('Service must implements ' . $interface);
            }
        } elseif (is_array($serviceOption) && isset($serviceOption['class'])) {
            $classname = $serviceOption['class'];
            $options = isset($serviceOption['options']) ? $serviceOption['options'] : [];
            if (is_null($interface) || is_a($classname, $interface, true)) {
                return $this->getServiceManager()->build($classname, $options);
            } else {
                throw new InvalidService('Service must implements ' . $interface);
            }
        }
        return null;
    }
}
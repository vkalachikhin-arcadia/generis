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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

namespace oat\generis\test\unit\common\persistence\sql\dbal;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use oat\generis\test\TestCase;

class PlatformTest extends TestCase
{

    public function testGetQueryBuilder()
    {
        $platform = $this->createInstance();
        $this->assertTrue($platform->getQueryBuilder() instanceof \Doctrine\DBAL\Query\QueryBuilder);
    }

    public function testMysqlPersistenceWithCharset()
    {
        $service = $this->getPersistenceManager();
        $service->registerPersistence('mysql', ['driver' => 'dbal', 'connection' => ['driver' => 'pdo_mysql']]);
        $this->assertEquals('utf8',$service->getOption('persistences')['mysql']['connection']['charset']);
    }

    public function testNotMysqlPersistenceWithCharset()
    {
        $service = $this->getPersistenceManager();
        $service->registerPersistence('notMysql', ['driver' => 'dbal', 'connection' => ['driver' => 'pdo_not_mysql']]);
        $this->assertArrayNotHasKey('charset',$service->getOption('persistences')['notMysql']['connection']);
    }

    public function testGetNowExpression()
    {
        $format = 'm:i:d:H:Y:s';
        $dbalPlatform = $this->getMockBuilder(AbstractPlatform::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDateTimeFormatString'])
            ->getMockForAbstractClass();
        $dbalPlatform->method('getDateTimeFormatString')->willReturn($format);

        /** @var Connection|\PHPUnit_Framework_MockObject_MockObject $dbalConnection */
        $dbalConnection = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDatabasePlatform'])
            ->getMock();
        $dbalConnection->method('getDatabasePlatform')->willReturn($dbalPlatform);

        $platform = new \common_persistence_sql_Platform($dbalConnection);
        $datetime = (new \DateTime('now', new \DateTimeZone('UTC')))->format($format);
        $this->assertEquals($datetime, $platform->getNowExpression());
    }

    /**
     * @return \common_persistence_sql_Platform
     */
    protected function createInstance()
    {
        if (!extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('Php extension pdo_sqlite is not installed.');
        }

        $driver = new \common_persistence_sql_dbal_Driver();
        $driver->connect('test_connection', ['connection' => ['url' => 'sqlite:///:memory:']]);
        return $driver->getPlatForm();
    }

    /**
     * @return \common_persistence_Manager
     */
    protected function getPersistenceManager()
    {
        $service = new \common_persistence_Manager();

        $service->setServiceLocator(
            $this->getServiceLocatorMock([])
        );
        return $service;
    }
}

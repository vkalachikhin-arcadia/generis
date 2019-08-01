<?php

namespace oat\generis\test\integration\common\persistence\sql\pdo;

use common_persistence_sql_pdo_sqlite_Driver;
use oat\generis\test\integration\common\persistence\sql\UpdateMultipleTestAbstract;

class UpdateMultipleTest extends UpdateMultipleTestAbstract
{
    /** @var common_persistence_sql_pdo_sqlite_Driver */
    protected $driver;

    public function setUpDriver()
    {
        if ($this->driver === null) {
            if (!extension_loaded('pdo_sqlite')) {
                $this->markTestSkipped('Php extension pdo_sqlite is not installed.');
            }

            $driver = new \common_persistence_sql_dbal_Driver();
            $driver->connect('test_connection', ['connection' => ['url' => 'sqlite:///:memory:']]);
            $this->driver = $driver;
        }
    }
}

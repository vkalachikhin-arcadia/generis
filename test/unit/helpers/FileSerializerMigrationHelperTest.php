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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

namespace oat\generis\test\unit\helpers;

use common_Config;
use common_session_SessionManager;
use common_test_TestUserSession;
use core_kernel_persistence_smoothsql_SmoothModel;
use oat\generis\Helper\FileSerializerMigrationHelper;
use oat\generis\model\fileReference\ResourceFileSerializer;
use oat\generis\model\fileReference\UrlFileSerializer;
use oat\generis\model\GenerisRdf;
use oat\generis\test\GenerisTestCase;
use oat\oatbox\filesystem\Directory;
use oat\oatbox\filesystem\File;
use oat\oatbox\filesystem\FileSystemService;
use oat\oatbox\filesystem\FileSystemHandler;

/**
 * Test cases for the File serializer migration script helper
 * @see \oat\generis\Helper\FileSerializerMigrationHelper
 */
class FileSerializerMigrationHelperTest extends GenerisTestCase
{

    const PARENT_RESOURCE_URI = 'http://www.tao.lu/Ontologies/generis.rdf#UnitTest';
    const PROPERTY_URI = 'http://www.tao.lu/Ontologies/generis.rdf#TestFile';

    /**
     * @var FileSerializerMigrationHelper
     */
    protected $fileMigrationHelper;

    /**
     * @var \core_kernel_classes_Class
     */
    protected $testClass;

    /**
     * @var core_kernel_persistence_smoothsql_SmoothModel
     */
    private $ontologyMock;

    /**
     * @var ResourceFileSerializer
     */
    private $resourceFileSerializer;

    /**
     * @var UrlFileSerializer
     */
    private $urlFileSerializer;

    /**
     * @var string
     */
    private $tempFileSystemId;

    /**
     * @var
     */
    private $tempDirectory;

    /**
     * @var File|Directory
     */
    private $testFile;

    /**
     * @var FileSystemService
     */
    private $fileSystemService;

    /**
     * Initialize test
     */
    public function setUp()
    {
        common_Config::load();
        common_session_SessionManager::startSession(new common_test_TestUserSession());
        $this->fileMigrationHelper = new FileSerializerMigrationHelper();
        $this->resourceFileSerializer = new ResourceFileSerializer();
        $this->urlFileSerializer = new UrlFileSerializer();

        $serviceLocator = $this->getServiceLocatorMock([FileSystemService::SERVICE_ID => $this->getMockFileSystem()]);
        $this->fileMigrationHelper->setServiceLocator($serviceLocator);
        $this->resourceFileSerializer->setServiceLocator($serviceLocator);
        $this->urlFileSerializer->setServiceLocator($serviceLocator);

        $this->ontologyMock = $this->getOntologyMock();
    }

    /**
     * Test the migration of a file resource
     */
    public function testResourceMigration()
    {
        try {
            $fileResource = $this->getFileResource();
            $this->fileMigrationHelper->migrateResource($fileResource, self::PARENT_RESOURCE_URI, self::PROPERTY_URI);
        } catch (\Exception $e) {
            if ($this->testFile !== null) {
                $this->testFile->delete();
            }
            throw new \Exception($e->getMessage());
        }

        self::assertSame($this->fileMigrationHelper->migrationInformation['migrated_count'], 1);

        $this->testFile->delete();
    }

    /**
     * Generate a file resource used for testing
     */
    private function getFileResource()
    {
        $dir = $this->getTempDirectory();

        $sampleFile = 'sampleFile.txt';
        $fileClass = $this->ontologyMock->getClass(GenerisRdf::CLASS_GENERIS_FILE);
        $this->testFile = $dir->getFile($sampleFile);
        $this->testFile->write($sampleFile, 'PHP Unit test file');

        if ($this->testFile instanceof File) {
            $filename = $this->testFile->getBasename();
            $filePath = dirname($this->testFile->getPrefix());
        } elseif ($this->testFile instanceof Directory) {
            $filename = '';
            $filePath = $this->testFile->getPrefix();
        } else {
            return false;
        }

        $resource = $fileClass->createInstanceWithProperties(
            [
                GenerisRdf::PROPERTY_FILE_FILENAME => $filename,
                GenerisRdf::PROPERTY_FILE_FILEPATH => $filePath,
                GenerisRdf::PROPERTY_FILE_FILESYSTEM => $this->ontologyMock->getResource($this->testFile->getFileSystemId()),
            ]
        );

        self::assertInstanceOf(FileSystemHandler::class, $this->resourceFileSerializer->unserialize($resource));

        $unitTestResource = $this->ontologyMock->getResource(self::PARENT_RESOURCE_URI);
        $testFileProperty = $this->ontologyMock->getProperty(self::PROPERTY_URI);
        $unitTestResource->setPropertyValue($testFileProperty, $unitTestResource);

        return $resource;
    }

    /**
     * Clean up after running tests
     */
    public function tearDown()
    {

    }

    /**
     * @return Directory
     */
    private function getTempDirectory()
    {
        if (!$this->tempDirectory) {
            $fileSystemService = $this->getMockFileSystem();
            $this->tempFileSystemId = uniqid('unit-test-', true);

            $adapters = $fileSystemService->getOption(FileSystemService::OPTION_ADAPTERS);
            if (class_exists('League\Flysystem\Memory\MemoryAdapter')) {
                $adapters[$this->tempFileSystemId] = [
                    'class' => \League\Flysystem\Memory\MemoryAdapter::class
                ];
            } else {
                $adapters[$this->tempFileSystemId] = [
                    'class' => FileSystemService::FLYSYSTEM_LOCAL_ADAPTER,
                    'options' => ['root' => '/tmp/testing']
                ];
            }
            $fileSystemService->setOption(FileSystemService::OPTION_ADAPTERS, $adapters);
            $fileSystemService->setOption(FileSystemService::OPTION_FILE_PATH, '/tmp/unit-test');

            $fileSystemService->setServiceLocator($this->getServiceLocatorMock([
                FileSystemService::SERVICE_ID => $fileSystemService
            ]));

            $this->tempDirectory = $fileSystemService->getDirectory($this->tempFileSystemId);
        }
        return $this->tempDirectory;
    }

    /**
     * @return FileSystemService
     */
    private function getMockFileSystem()
    {
        if ($this->fileSystemService === null) {
            $this->fileSystemService = $this->getServiceLocatorMock([FileSystemService::SERVICE_ID => new FileSystemService()])->get(FileSystemService::SERVICE_ID);
        }

        return $this->fileSystemService;
    }
}
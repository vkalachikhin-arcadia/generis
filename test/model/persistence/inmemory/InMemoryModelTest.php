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
 * Copyright (c) (original work) 2015 Open Assessment Technologies SA
 *
 */
namespace oat\generis\test\model\kernel\persistence\inmemory;

use oat\generis\test\GenerisPhpUnitTestRunner;
use oat\generis\model\kernel\persistence\inmemory\InMemoryModel;
//use oat\generis\model\kernel\persistence\file\FileModel;
//use oat\generis\model\kernel\persistence\file\FileModel;
//use oat\generis\core\kernel\persistence\file\FileModel;

use \common_exception_MissingParameter;

class InMemoryModelTest extends GenerisPhpUnitTestRunner
{
    const MODEL_ID = '1';
    /**
     *
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    public function setUp()
    {
        GenerisPhpUnitTestRunner::initTest();
    }


    public function testConstruct()
    {
        //
        $options = array(
            'readable' => array(
                0 => '1',
                1 => '2',
                2 => '3',
                3 => '4',
                4 => '5',
                5 => '6',
                6 => '7',
                7 => '8',
                8 => '9',
                9 => '10',
                10 => '11',
                11 => '12',
                12 => '13',
                13 => '14',
                14 => '15',
                15 => '16',
                16 => '17',
                17 => '18'
            ),
            'writeable' => array(0 => '1'),
            'addTo' => self::MODEL_ID
        );

        $model = new InMemoryModel($options);
        //$model = new \oat\generis\core\kernel\persistence\file\FileModel();

        $this->assertInstanceOf('oat\generis\model\data\Model', $model);
        $this->assertInstanceOf('oat\oatbox\Configurable', $model);

        return $model;
    }
    /**
     * @depends testConstruct
     *
     * @author Lionel Lecaque, lionel@taotesting.com
     * @param InMemoryModel $model
     */
    public function testGetRdfInterface($model)
    {
        $this->assertInstanceOf('oat\generis\model\kernel\persistence\inmemory\InMemoryRdf', $model->getRdfInterface());
    }

    /**
     * @depends testConstruct
     *
     * @author Lionel Lecaque, lionel@taotesting.com
     * @param InMemoryModel $model
     */
    public function testGetRdfsInterface($model)
    {
        $this->assertInstanceOf('oat\generis\model\kernel\persistence\inmemory\InMemoryRdfs', $model->getRdfsInterface());
    }

    /**
     *
     * @author Lionel Lecaque, lionel@taotesting.com
     * @return array
     */
    public function modelProvider()
    {
        $dir = GenerisPhpUnitTestRunner::getSampleDir();
        return array(
            array(
                6,
                $dir . '/rdf/generis.rdf'
            ),
            array(
                4,
                $dir . '/rdf/widget.rdf'
            ),
            array(
                100,
                $dir . '/rdf/nobase.rdf'
            )
        );
    }

    /**
     * @depends testConstruct
     *
     * @param InMemoryModel $model
     */
    public function testAdd(InMemoryModel $model)
    {
        $tripleNoLg = new \core_kernel_classes_Triple();
        $tripleNoLg->subject = 'testTripleSubject';
        $tripleNoLg->id = 1;
        $tripleNoLg->modelid = 1;
        $tripleNoLg->predicate = 'testTriplePredicate';
        $tripleNoLg->object = 'testTripleObject';

        $tripleDefLg = clone $tripleNoLg;
        $tripleDefLg->lg = '';

        $tripleLg = clone $tripleNoLg;
        $tripleLg->lg = 'en_US';

        $model->add($tripleNoLg);
        $model->add($tripleDefLg);
        $model->add($tripleLg);

        $data = $model->getData();
        $this->assertEquals(2, count($data));
        foreach( $data as $triple ){
            $this->assertInstanceOf('\core_kernel_classes_Triple', $triple);
        }
    }

    /**
     * @depends testConstruct
     *
     * @param InMemoryModel $model
     */
    public function testRemove($model)
    {
        $tripleNoLg = new \core_kernel_classes_Triple();
        $tripleNoLg->subject = 'testTripleSubject';
        $tripleNoLg->id = 1;
        $tripleNoLg->modelid = 1;
        $tripleNoLg->predicate = 'testTriplePredicate';
        $tripleNoLg->object = 'testTripleObject';

        $tripleLg = clone $tripleNoLg;
        $tripleLg->lg = 'en_US';

        $model->add($tripleLg);
        $model->add($tripleNoLg);

        $model->remove($tripleNoLg);
        $data = $model->getData();

        $this->assertEquals(1, count($data));
        $this->assertEquals($tripleLg, current($data));
    }

    /**
     * @depends testConstruct
     *
     * @param InMemoryModel $model
     */
    public function testGetNewTripleModelId($model)
    {
        $this->assertEquals(self::MODEL_ID, $model->getNewTripleModelId());
    }


    public function testGetWhere()
    {

    }

//    /**
//     * @dataProvider modelProvider
//     *
//     * @author Lionel Lecaque, lionel@taotesting.com
//     */
//    public function testGetModelIdFromXml($id, $file)
//    {
//        try {
//            $modelid = FileModel::getModelIdFromXml($file);
//            $this->assertEquals($id, $modelid);
//        } catch (\Exception $e) {
//            $this->assertInstanceOf('\common_exception_Error', $e);
//            if ($id == 100) {
//                $this->assertContains('has to be defined with the "xml:base" attribute of the ROOT node', $e->getMessage());
//            } else {
//                $this->fail('unexpected error');
//            }
//        }
//    }
}

?>
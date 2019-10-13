<?php

/**
 * ModelCreatorのテスト
 */

namespace Test\System\Util;

use System\Database\Connection;
use System\Exception\SystemException;
use System\Util\ModelCreator;
use Test\Mock;
use Test\TestHelper;

class TestModelCreator extends TestHelper
{
    /**
     * __construct
     */
    public function __constructTest()
    {
        $this->compareInstance('System\Util\ModelCreator', new ModelCreator(new Connection()));
    }

    private function getModelCreator()
    {
        $modelCreator = Mock::m('System\Util\ModelCreator');

        $prepare = Mock::m()
            ->_setMethod('setFetchMode')
            ->_setArgs(\PDO::FETCH_ASSOC)
            ->_setReturn()
            ->e();

        $prepare->_setMethod('fetchAll')
            ->_setArgs()
            ->_setReturn([
                ['dummy' => 'hoge_tbl'],
                ['dummy' => 'fuga_tbl']
            ])
            ->e();

        $pdo = Mock::m()
            ->_setMethod('query')
            ->_setArgs('SHOW TABLES')
            ->_setReturn($prepare)
            ->e();

        $connection = Mock::m('System\Database\Connection')
            ->_setMethod('getAuto')
            ->_setArgs()
            ->_setReturn($pdo)
            ->e();

        $modelCreator->connection = $connection;

        return $modelCreator;
    }

    /**
     * initialize
     * getModelName
     * getTableName
     */
    public function initializeAndGetModelNameAndGetTableNameTest()
    {
        $modelCreator = $this->getModelCreator();
        $modelCreator->initialize('App\Model\Fuga');

        $this->compareValue('Fuga', $modelCreator->getModelName(), 'モデル名');
        $this->compareValue('fuga_tbl', $modelCreator->tableName, 'テーブル名');

        try {
            $modelCreator->initialize('App\Model\Piyo');
            $this->throwError('例外が発生すべきか所で発生していない');
        } catch (SystemException $e) {
            $this->compareException('名前空間に対応する', $e, 'テーブルが存在しない場合');
        }
    }

    /**
     * makeDirectory
     */
    public function makeDirectoryTest()
    {
        $modelCreator = new ModelCreator(new Connection());
        $proprety = new \ReflectionProperty($modelCreator, 'explodedNamespaceList');
        $proprety->setAccessible(true);

        $namespace = str_replace(PUBLIC_DIR, '', MODEL_DIR) . 'Hoge/Fuga';
        $explodedNamespaceList = explode('/', $namespace);
        $proprety->setValue($modelCreator, $explodedNamespaceList);

        $this->compareValue(null, $modelCreator->makeDirectory());
    }

    /**
     * parseColumn
     * getType
     * getCastType
     */
    public function parseColumnAndGetTypeAndGetCastTypeTest()
    {
        $reflection   = new \ReflectionClass('System\Util\ModelCreator');
        $modelCreator = $reflection->newInstanceWithoutConstructor();

        $prepare = Mock::m()
            ->_setMethod('setFetchMode')
            ->_setArgs(\PDO::FETCH_ASSOC)
            ->_setReturn()
            ->e();

        $prepare->_setMethod('fetchAll')
            ->_setArgs()
            ->_setReturn([
                [
                    'Key'     => 'PRI',
                    'Field'   => 'id',
                    'Default' => null,
                    'Type'    => 'int'
                ],
                [
                    'Key'     => null,
                    'Field'   => 'name',
                    'Default' => 'hoge',
                    'Type'    => 'string'
                ],
                [
                    'Key'     => null,
                    'Field'   => 'time',
                    'Default' => null,
                    'Type'    => 'time'
                ],
                [
                    'Key'     => null,
                    'Field'   => 'date',
                    'Default' => 'CURRENT_TIMESTAMP',
                    'Type'    => 'date'
                ],
                [
                    'Key'     => null,
                    'Field'   => 'coordinate',
                    'Default' => null,
                    'Type'    => 'geometry'
                ]
            ])
            ->e();

        $pdo = Mock::m()
            ->_setMethod('query')
            ->_setArgs('SHOW COLUMNS FROM hoge_tbl')
            ->_setReturn($prepare)
            ->e();

        $connection = Mock::m('System\Database\Connection')
            ->_setMethod('getAuto')
            ->_setArgs()
            ->_setReturn($pdo)
            ->e();

        $method = new \ReflectionMethod($modelCreator, 'parseColumn');
        $method->setAccessible(true);

        $property = new \ReflectionProperty($modelCreator, 'tableName');
        $property->setAccessible(true);
        $property->setValue($modelCreator, 'hoge_tbl');

        $property = new \ReflectionProperty($modelCreator, 'connection');
        $property->setAccessible(true);
        $property->setValue($modelCreator, $connection);

        $method->invoke($modelCreator);

        $property = new \ReflectionProperty($modelCreator, 'columnInfoList');
        $property->setAccessible(true);

        $expected = [
            [
                'column'  => 'id',
                'type'    => 'int',
                'cast'    => 'intval',
                'getter'  => 'getId',
                'default' => 'null'
            ],
            [
                'column'  => 'name',
                'type'    => 'string',
                'cast'    => 'strval',
                'getter'  => 'getName',
                'default' => 'hoge'
            ],
            [
                'column'  => 'time',
                'type'    => '\DateTime',
                'cast'    => 'new \DateTime',
                'getter'  => 'getTime',
                'default' => 'null'
            ],
            [
                'column'  => 'date',
                'type'    => '\DateTime',
                'cast'    => 'new \DateTime',
                'getter'  => 'getDate',
                'default' => 'new \DateTime()'
            ],
            [
                'column'  => 'coordinate',
                'type'    => 'Point',
                'cast'    => 'new Point',
                'getter'  => 'getCoordinate',
                'default' => 'null'
            ]
        ];

        $this->compareValue($expected, $property->getValue($modelCreator));
    }

    /**
     * getProperty
     * getSetter
     * getGetter
     */
    public function getPropertyAndGetSetterAndGetGetterTest()
    {
        $modelCreator = Mock::m('System\Util\ModelCreator');

        $this->compareValue('', $modelCreator->getProperty(), 'getProprety');
        $this->compareValue('', $modelCreator->getSetter(), 'getSetter');
        $this->compareValue('', $modelCreator->getGetter(), 'getGetter');
    }

    /**
     * existEntity
     */
    public function existEntityTest()
    {
        $modelCreator = Mock::m('System\Util\ModelCreator');
        $modelCreator->explodedNamespaceList = ['hoge', 'fuga'];
        $this->compareValue(false, $modelCreator->existEntity());
    }

    /**
     * createEntity
     */
    public function createEntityTest()
    {
        $modelCreator = Mock::m('System\Util\ModelCreator');
        $modelCreator->explodedNamespaceList = ['hoge', 'fuga', 'piyo'];
        $this->compareValue(true, $modelCreator->createEntity());
    }

    /**
     * createSkeleton
     */
    public function createSkeletonTest()
    {
        $modelCreator = Mock::m('System\Util\ModelCreator');
        $modelCreator->_setMethod('parseColumn')
            ->_setArgs()
            ->_setReturn()
            ->e();
        $modelCreator->explodedNamespaceList = ['hoge', 'fuga', 'piyo'];
        $this->compareValue(true, $modelCreator->createEntity());
    }
}

namespace System\Util;

/**
 * mkdirのオーバーライド
 */
function mkdir($path)
{
    return null;
}

/**
 * file_put_contentsのオーバーライド
 */
function file_put_contents()
{
    return true;
}

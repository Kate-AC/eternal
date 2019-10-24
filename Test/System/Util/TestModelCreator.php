<?php

/**
 * ModelCreatorのテスト
 */

namespace Test\System\Util;

use System\Database\Connection;
use System\Exception\SystemException;
use System\Util\ModelCreator;
use Phantom\Phantom;
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
        $modelCreator = Phantom::m('System\Util\ModelCreator');

        $prepare = Phantom::m()
            ->setMethod('setFetchMode')
            ->setArgs(\PDO::FETCH_ASSOC)
            ->setReturn()
            ->exec();

        $prepare
            ->setMethod('fetchAll')
            ->setArgs()
            ->setReturn([
                ['dummy' => 'hoge'],
                ['dummy' => 'fuga']
            ])
            ->exec();

        $pdo = Phantom::m()
            ->setMethod('query')
            ->setArgs('SHOW TABLES')
            ->setReturn($prepare)
            ->exec();

        $connection = Phantom::m('System\Database\Connection')
            ->setMethod('getAuto')
            ->setArgs()
            ->setReturn($pdo)
            ->exec();

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
        $this->compareValue('fuga', $modelCreator->tableName, 'テーブル名');

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
        $modelCreator = Phantom::m('System\Util\ModelCreator');

        $prepare = Phantom::m()
            ->setMethod('setFetchMode')
            ->setArgs(\PDO::FETCH_ASSOC)
            ->setReturn()
            ->exec();

        $prepare->setMethod('fetchAll')
            ->setArgs()
            ->setReturn([
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
            ->exec();

        $pdo = Phantom::m()
            ->setMethod('query')
            ->setArgs('SHOW COLUMNS FROM hoge')
            ->setReturn($prepare)
            ->exec();

        $connection = Phantom::m('System\Database\Connection')
            ->setMethod('getAuto')
            ->setArgs()
            ->setReturn($pdo)
            ->exec();

        $modelCreator->tableName = 'hoge';
        $modelCreator->modelName = 'Hoge';
        $modelCreator->connection = $connection;
        $modelCreator->parseColumn();

        $expected = [
            [
                'column'  => 'id',
                'type'    => 'int',
                'cast'    => 'intval',
                'getter'  => 'getId',
                'setter'  => 'setId',
                'default' => 'null'
            ],
            [
                'column'  => 'name',
                'type'    => 'string',
                'cast'    => 'strval',
                'getter'  => 'getName',
                'setter'  => 'setName',
                'default' => 'hoge'
            ],
            [
                'column'  => 'time',
                'type'    => '\DateTime',
                'cast'    => 'new \DateTime',
                'getter'  => 'getTime',
                'setter'  => 'setTime',
                'default' => 'null'
            ],
            [
                'column'  => 'date',
                'type'    => '\DateTime',
                'cast'    => 'new \DateTime',
                'getter'  => 'getDate',
                'setter'  => 'setDate',
                'default' => 'new \DateTime()'
            ],
            [
                'column'  => 'coordinate',
                'type'    => 'Point',
                'cast'    => 'new Point',
                'getter'  => 'getCoordinate',
                'setter'  => 'setCoordinate',
                'default' => 'null'
            ]
        ];

        $this->compareValue($expected, $modelCreator->columnInfoList);
    }

    /**
     * getProperty
     * getSetter
     * getGetter
     */
    public function getPropertyAndGetSetterAndGetGetterTest()
    {
        $modelCreator = Phantom::m('System\Util\ModelCreator');

        $this->compareValue('', $modelCreator->getProperty(), 'getProprety');
        $this->compareValue('', $modelCreator->getSetter(), 'getSetter');
        $this->compareValue('', $modelCreator->getGetter(), 'getGetter');
    }

    /**
     * existEntity
     */
    public function existEntityTest()
    {
        $modelCreator = Phantom::m('System\Util\ModelCreator');
        $modelCreator->explodedNamespaceList = ['hoge', 'fuga'];
        $this->compareValue(false, $modelCreator->existEntity());
    }

    /**
     * createEntity
     */
    public function createEntityTest()
    {
        $modelCreator = Phantom::m('System\Util\ModelCreator');
        $modelCreator->explodedNamespaceList = ['hoge', 'fuga', 'piyo'];
        $this->compareValue(true, $modelCreator->createEntity());
    }

    /**
     * createSkeleton
     */
    public function createSkeletonTest()
    {
        $modelCreator = new ModelCreator(new Connection());
        $property = new \ReflectionProperty($modelCreator, 'explodedNamespaceList');
        $property->setAccessible(true);
        $property->setValue($modelCreator, ['hoge', 'fuga', 'piyo']);

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
function file_put_contents($path, $entry)
{
    return true;
}

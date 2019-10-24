<?php

/**
 * QueryFactoryTraitのテスト
 */

namespace Test\System\Database\Query;

use System\Database\Collect;
use System\Exception\DatabaseException;
use Phantom\Phantom;
use Test\TestHelper;

use Test\TestModel;

class TestQueryFactoryTrait extends TestHelper
{
    /**
     * makeDependencyList
     */
    public function makeDependencyListTest()
    {
        $reflection = new \ReflectionClass('System\Database\Query\SelectQuery');
        $instance   = $reflection->newInstanceWithoutConstructor();

        $property = new \ReflectionProperty($instance, 'formatedJoin');
        $property->setAccessible(true);

        $formatedJoin = [
            [
                'join' => [
                    'table' => 'fuga_tbl',
                    'as'    => null
                ],
                'on' => [
                    [
                        'a' => ['table' => 'fuga_tbl', 'column' => 'id'],
                        'b' => ['table' => 'hoge_tbl', 'column' => 'id']
                    ]
                ]
            ],
            [
                'join' => [
                    'table' => 'piyo_tbl',
                    'as'    => null
                ],
                'on' => [
                    [
                        'a' => ['table' => 'piyo_tbl', 'column' => 'id'],
                        'b' => ['table' => 'hoge_tbl', 'column' => 'id']
                    ]
                ]
            ]
        ];

        $property->setValue($instance, $formatedJoin);

        $tableAsName = [
            'hoge_tbl' => 'hoge_tbl',
            'fuga_tbl' => 'fuga_tbl',
            'piyo_tbl' => 'piyo_tbl'
        ];

        $property = new \ReflectionProperty($instance, 'tableAsName');
        $property->setAccessible(true);
        $property->setValue($instance, $tableAsName);

        $method = new \ReflectionMethod($instance, 'makeDependencyList');
        $method->setAccessible(true);
        $method->invoke($instance);

        $property = new \ReflectionProperty($instance, 'dependencyList');
        $property->setAccessible(true);

        $dependencyList = [
            'hoge_tbl' => [
                'fuga_tbl',
                'piyo_tbl'
            ],
            'fuga_tbl' => [],
            'piyo_tbl' => []
        ];

        $this->compareValue($dependencyList, $property->getValue($instance), 'makeDependencyList');
    }

    /**
     * union
     */
    public function unionTest()
    {
        $query = Phantom::m('System\Database\Query\SelectQuery');
        $query->asSelf = 'hoge';

        $dependencyList = [
            'hoge' => ['fuga', 'piyo'],
            'fuga' => ['mosu'],
            'mosu' => [],
            'piyo' => []
        ];

        $entityList = [
            'hoge' => new \stdClass(),
            'fuga' => new \stdClass(),
            'mosu' => new \stdClass(),
            'piyo' => new \stdClass()
        ];

        $entityList['hoge']->name = 'Hoge';
        $entityList['fuga']->name = 'Fuga';
        $entityList['mosu']->name = 'Mosu';
        $entityList['piyo']->name = 'Piyo';

        $created = $entityList['hoge'];
        $created->Fuga = $entityList['fuga'];
        $created->Piyo = $entityList['piyo'];
        $created->Fuga->Mosu = $entityList['mosu'];

        $this->compareValue($created, $query->union($dependencyList, $entityList));
    }

    /**
     * unite
     */
    public function uniteTest()
    {
        $query = Phantom::m('System\Database\Query\SelectQuery');
        $query->asSelf = 'hoge';

        $container = Phantom::m('System\Core\Di\Container')
            ->setMethod('getByTable')
            ->setArgs('hoge')
            ->setReturn('\Test\TestModel')
            ->exec();
        $container->setMethod('getByTable')
            ->setArgs('fuga')
            ->setReturn('\Test\TestModel')
            ->exec();
        $container->setMethod('getByTable')
            ->setArgs('piyo')
            ->setReturn('\Test\TestModel')
            ->exec();
        $query->container = $container;

        $query->tableAsName = [
            'hoge' => 'hoge',
            'fuga' => 'fuga',
            'piyo' => 'piyo'
        ];

        $query->formatedJoin = [
            [
                'join' => [
                    'table' => 'fuga',
                    'as'    => null
                ],
                'on' => [
                    [
                        'a' => ['table' => 'fuga', 'column' => 'id'],
                        'b' => ['table' => 'hoge', 'column' => 'id']
                    ]
                ]
            ],
            [
                'join' => [
                    'table' => 'piyo',
                    'as'    => null
                ],
                'on' => [
                    [
                        'a' => ['table' => 'piyo', 'column' => 'id'],
                        'b' => ['table' => 'hoge', 'column' => 'id']
                    ]
                ]
            ]
        ];

        $resultList = [
            [
                '_collect___COUNT(piyo.id)' => 100,
                'hoge___id'   => '2',
                'fuga___id'   => '2',
                'fuga___name' => 'AAA',
                'piyo___id'   => '2',
                'piyo___name' => 'aaa'
            ],
            [
                '_collect___COUNT(piyo.id)' => 200,
                'hoge___id'   => '3',
                'fuga___id'   => '3',
                'fuga___name' => 'BBB',
                'piyo___id'   => '3',
                'piyo___name' => 'bbb'
            ]
        ];

        $created          = TestModel::make(['id' => '2']);
        $created->Fuga    = TestModel::make(['id' => '2', 'name' => 'AAA']);
        $created->Piyo    = TestModel::make(['id' => '2', 'name' => 'aaa']);
        $created->Collect = new Collect(['COUNT(piyo.id)' => 100]);

        $created2          = TestModel::make(['id' => '3']);
        $created2->Fuga    = TestModel::make(['id' => '3', 'name' => 'BBB']);
        $created2->Piyo    = TestModel::make(['id' => '3', 'name' => 'bbb']);
        $created2->Collect = new Collect(['COUNT(piyo.id)' => 200]);

        $this->compareValueLax([$created, $created2], $query->unite($resultList));

        try {
            $query->unite($resultList, 'notExistKey');
            $this->throwError('例外が発生すべき箇所で発生していない');
        } catch (DatabaseException $e) {
            $this->compareException('メソッドが存在しません', $e, '存在しないkeyを指定');
        }

        try {
            $query->unite($resultList, 'name');
            $this->throwError('例外が発生すべき箇所で発生していない');
        } catch (DatabaseException $e) {
            $this->compareException('添え字に使用できない', $e, 'nullの結果を返すkeyを指定');
        }
    }

    /**
     * unionArray
     */
    public function unionArrayTest()
    {
        $query = Phantom::m('System\Database\Query\SelectQuery');
        $query->asSelf = 'hoge';

        $dependencyList = [
            'hoge' => ['fuga', 'piyo'],
            'fuga' => ['mosu'],
            'mosu' => [],
            'piyo' => []
        ];

        $classList = [
            'hoge' => ['Hoge' => []],
            'fuga' => ['Fuga' => []],
            'mosu' => ['Mosu' => []],
            'piyo' => ['Piyo' => []]
        ];

        $created = $classList['hoge'];

        $created['Hoge']['Fuga']         = $classList['fuga']['Fuga'];
        $created['Hoge']['Piyo']         = $classList['piyo']['Piyo'];
        $created['Hoge']['Fuga']['Mosu'] = $classList['mosu']['Mosu'];

        $this->compareValue($created, $query->unionArray($dependencyList, $classList));
    }

    /**
     * uniteArray
     */
    public function uniteArrayTest()
    {
        $query = Phantom::m('System\Database\Query\SelectQuery');
        $query->asSelf = 'hoge';

        $query->tableAsName = [
            'hoge' => 'hoge',
            'fuga' => 'fuga',
            'piyo' => 'piyo'
        ];

        $query->formatedJoin = [
            [
                'join' => [
                    'table' => 'fuga',
                    'as'    => null
                ],
                'on' => [
                    [
                        'a' => ['table' => 'fuga', 'column' => 'id'],
                        'b' => ['table' => 'hoge', 'column' => 'id']
                    ]
                ]
            ],
            [
                'join' => [
                    'table' => 'piyo',
                    'as'    => null
                ],
                'on' => [
                    [
                        'a' => ['table' => 'piyo', 'column' => 'id'],
                        'b' => ['table' => 'hoge', 'column' => 'id']
                    ]
                ]
            ]
        ];

        $resultList = [
            [
                'hoge___id'   => '2',
                'hoge___name' => null,
                'fuga___id'   => '2',
                'fuga___name' => 'AAA',
                'piyo___id'   => '2',
                'piyo___name' => 'aaa',
                'piyo___POINT(1.0 2.0)' => 'POINT(1.0 2.0)'
            ],
            [
                'hoge___id'   => '3',
                'hoge___name' => null,
                'fuga___id'   => '3',
                'fuga___name' => 'BBB',
                'piyo___id'   => '3',
                'piyo___name' => 'bbb',
                'piyo___POINT(3.0 4.0)' => 'POINT(3.0 4.0)'
            ]
        ];

        $created = ['Hoge' => ['id' => '2', 'name' => null]];
        $created['Hoge']['Fuga'] = ['id' => '2', 'name' => 'AAA'];
        $created['Hoge']['Piyo'] = ['id' => '2', 'name' => 'aaa'];
        $created['Hoge']['Piyo']['POINT(1.0 2.0)'] = ['Point' => ['lng' => '1.0', 'lat' => '2.0']];

        $created2 = ['Hoge' => ['id' => '3', 'name' => null]];
        $created2['Hoge']['Fuga'] = ['id' => '3', 'name' => 'BBB'];
        $created2['Hoge']['Piyo'] = ['id' => '3', 'name' => 'bbb'];
        $created2['Hoge']['Piyo']['POINT(3.0 4.0)'] = ['Point' => ['lng' => '3.0', 'lat' => '4.0']];

        $this->compareValue([$created, $created2], $query->uniteArray($resultList), 'key未指定');
        $this->compareValue([2 => $created, 3 => $created2], $query->uniteArray($resultList, 'id'), 'key指定');

        try {
            $query->uniteArray($resultList, 'notExistKey');
            $this->throwError('例外が発生すべき箇所で発生していない');
        } catch (DatabaseException $e) {
            $this->compareException('添え字が存在しません', $e, '存在しないkeyを指定');
        }

        try {
            $query->uniteArray($resultList, 'name');
            $this->throwError('例外が発生すべき箇所で発生していない');
        } catch (DatabaseException $e) {
            $this->compareException('nullの値が存在します', $e, 'nullの結果を返すkeyを指定');
        }
    }
}

namespace Test;

use System\Database\Model;

class TestModel extends Model
{
    protected $id;

    protected $name;

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public static function make(array $properties)
    {
        if (isset($properties['id'])) {
            if (is_numeric($properties['id'])) {
                $properties['id'] = intval($properties['id']);
            }
        } else {
            $properties['id'] = null;
        }

        if (isset($properties['name'])) {
            $properties['name'] = strval($properties['name']);
        } else {
            $properties['name'] = null;
        }

        $instance = new static();
        return $instance($properties);
    }
}

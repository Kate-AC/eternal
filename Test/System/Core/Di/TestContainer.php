<?php

/**
 * Containerのテスト
 */

namespace Test\System\Core\Di;

use Phantom\Phantom;
use Test\TestHelper;
use System\Core\AutoLoader;
use System\Core\Cache;
use System\Core\Di\Container;
use System\Core\Di\DependencyInjector;
use System\Core\Di\DependencyDto;
use System\Core\Di\DependencySearcher;
use System\Util\FilePathSearcher;

class TestContainer extends TestHelper
{
    /**
     * __construct
     */
    public function __constructTest()
    {
        $container = new Container(
            new AutoLoader(new FilePathSearcher()),
            new Cache(),
            new DependencySearcher(new FilePathSearcher()),
            new DependencyInjector(new Cache())
        );

        $this->compareInstance($container,
            (new \ReflectionClass('System\Core\Di\Container'))->newInstanceWithoutConstructor()
        );
    }

    /**
     * get
     */
    public function getTest()
    {
        $container = Phantom::m('System\Core\Di\Container');

        $result            = new \stdClass();
        $namespace         = 'Test\Test';
        $dependencyDtoList = [
            new DependencyDto('a', ['b', 'c']),
            new DependencyDto('d', ['e', 'f'])
        ];

        $container->cache = Phantom::m('System\Core\Cache')
            ->setMethod('getCache')
            ->setArgs($namespace)
            ->setReturn(null)
            ->exec();

        $container->dependencySearcher = Phantom::m('System\Core\Di\DependencySearcher')
            ->setMethod('search')
            ->setArgs($namespace)
            ->setReturn($dependencyDtoList)
            ->exec();

        $dependencyInjector = Phantom::m('System\Core\Di\DependencyInjector')
            ->setMethod('create')
            ->setArgs($dependencyDtoList)
            ->setReturn(null)
            ->exec();
        $dependencyInjector
            ->setMethod('getResolvedClass')
            ->setArgs($namespace)
            ->setReturn($result)
            ->exec();
        $container->dependencyInjector = $dependencyInjector;

        $this->compareValue($result, $container->get($namespace), 'キャッシュが存在しない場合');

        $container->cache = Phantom::m('System\Core\Cache')
            ->setMethod('getCache')
            ->setArgs($namespace)
            ->setReturn($result)
            ->exec();

        $this->compareValue($result, $container->get($namespace), 'キャッシュが存在する場合');
    }

    /**
     * getByTableTest
     */
    public function getByTableTest()
    {
        $container = Phantom::m('System\Core\Di\Container');

        $result           = new \stdClass();
        $table            = 'test_tbl';
        $namespace        = 'Test\Test';
        $dependencyDtoList = [
            new DependencyDto('a', ['b', 'c']),
            new DependencyDto('d', ['e', 'f'])
        ];

        $cache = Phantom::m('System\Core\Cache')
            ->setMethod('getCache')
            ->setArgs($table)
            ->setReturn(null)
            ->exec();
        $cache
            ->setMethod('setCache')
            ->setArgs($table, $result)
            ->setReturn(null)
            ->exec();
        $container->cache = $cache;

        $dependencySearcher = Phantom::m('System\Core\Di\DependencySearcher')
            ->setMethod('searchByTable')
            ->setArgs($table)
            ->setReturn($namespace)
            ->exec();
        $dependencySearcher
            ->setMethod('getDependencyDtoList')
            ->setArgs()
            ->setReturn($dependencyDtoList)
            ->exec();
        $container->dependencySearcher = $dependencySearcher;

        $dependencyInjector = Phantom::m('System\Core\Di\DependencyInjector')
            ->setMethod('create')
            ->setArgs($dependencyDtoList)
            ->setReturn(null)
            ->exec();
        $dependencyInjector
            ->setMethod('getResolvedClass')
            ->setArgs($namespace)
            ->setReturn($result)
            ->exec();
        $container->dependencyInjector = $dependencyInjector;

        $this->compareValue($result, $container->getByTable($table), 'キャッシュが存在しない場合');

        $container->cache = Phantom::m('System\Core\Cache')
            ->setMethod('getCache')
            ->setArgs($table)
            ->setReturn($result)
            ->exec();

        $this->compareValue($result, $container->getByTable($table), 'キャッシュが存在する場合');
    }
}

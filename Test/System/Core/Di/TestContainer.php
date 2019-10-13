<?php

/**
 * Containerのテスト
 */

namespace Test\System\Core\Di;

use Test\Mock;
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
        $container = Mock::m('System\Core\Di\Container');

        $result            = new \stdClass();
        $namespace         = 'Test\Test';
        $dependencyDtoList = [
            new DependencyDto('a', ['b', 'c']),
            new DependencyDto('d', ['e', 'f'])
        ];

        $container->cache = Mock::m('System\Core\Cache')
            ->_setMethod('getCache')
            ->_setArgs($namespace)
            ->_setReturn(null)
            ->e();

        $container->dependencySearcher = Mock::m('System\Core\Di\DependencySearcher')
            ->_setMethod('search')
            ->_setArgs($namespace)
            ->_setReturn($dependencyDtoList)
            ->e();

        $dependencyInjector = Mock::m('System\Core\Di\DependencyInjector')
            ->_setMethod('create')
            ->_setArgs($dependencyDtoList)
            ->_setReturn(null)
            ->e();
        $dependencyInjector->_setMethod('getResolvedClass')
            ->_setArgs($namespace)
            ->_setReturn($result)
            ->e();
        $container->dependencyInjector = $dependencyInjector;

        $this->compareValue($result, $container->get($namespace), 'キャッシュが存在しない場合');

        $container->cache = Mock::m('System\Core\Cache')
            ->_setMethod('getCache')
            ->_setArgs($namespace)
            ->_setReturn($result)
            ->e();

        $this->compareValue($result, $container->get($namespace), 'キャッシュが存在する場合');
    }

    /**
     * getByTableTest
     */
    public function getByTableTest()
    {
        $container = Mock::m('System\Core\Di\Container');

        $result           = new \stdClass();
        $table            = 'test_tbl';
        $namespace        = 'Test\Test';
        $dependencyDtoList = [
            new DependencyDto('a', ['b', 'c']),
            new DependencyDto('d', ['e', 'f'])
        ];

        $cache = Mock::m('System\Core\Cache')
            ->_setMethod('getCache')
            ->_setArgs($table)
            ->_setReturn(null)
            ->e();
        $cache->_setMethod('setCache')
            ->_setArgs($table, $result)
            ->_setReturn(null)
            ->e();
        $container->cache = $cache;

        $dependencySearcher = Mock::m('System\Core\Di\DependencySearcher')
            ->_setMethod('searchByTable')
            ->_setArgs($table)
            ->_setReturn($namespace)
            ->e();
        $dependencySearcher
            ->_setMethod('getDependencyDtoList')
            ->_setArgs()
            ->_setReturn($dependencyDtoList)
            ->e();
        $container->dependencySearcher = $dependencySearcher;

        $dependencyInjector = Mock::m('System\Core\Di\DependencyInjector')
            ->_setMethod('create')
            ->_setArgs($dependencyDtoList)
            ->_setReturn(null)
            ->e();
        $dependencyInjector->_setMethod('getResolvedClass')
            ->_setArgs($namespace)
            ->_setReturn($result)
            ->e();
        $container->dependencyInjector = $dependencyInjector;

        $this->compareValue($result, $container->getByTable($table), 'キャッシュが存在しない場合');

        $container->cache = Mock::m('System\Core\Cache')
            ->_setMethod('getCache')
            ->_setArgs($table)
            ->_setReturn($result)
            ->e();

        $this->compareValue($result, $container->getByTable($table), 'キャッシュが存在する場合');
    }
}

<?php

/**
 * Dispatcherのテスト
 */

namespace Test\System\Core\Route;

use System\Core\Di\Container;
use System\Core\Extend\ExtendProtocol;
use System\Core\Route\Request;
use System\Core\Route\Route;
use System\Core\Route\Dispatcher;
use System\Log\SystemErrorLogger;
use Phantom\Phantom;
use Test\TestHelper;

class TestDispatcher extends TestHelper
{
    /**
     * __construct
     */
    public function __constructTest()
    {
        $route = new Route();
        $route->set(['/' => 'hoge@fuga']);

        $this->compareInstance(
            'System\Core\Route\Dispatcher',
            new Dispatcher($this->container, new ExtendProtocol(), new Request($route), new SystemErrorLogger)
        );
    }

    /**
     * start
     */
    public function startTest()
    {
        $firstProcess = Phantom::m()
            ->setMethod('execute')
            ->setArgs()
            ->setReturn(null)
            ->exec();

        $namespace = 'Test\Hoge\Fuga';

        $request = Phantom::m('System\Core\Route\Request')
            ->setMethod('getControllerNameSpace')
            ->setArgs()
            ->setReturn($namespace)
            ->exec();

        $container = Phantom::m('System\Core\Di\Container')
            ->setMethod('get')
            ->setArgs(FIRST_PROCESS_CLASS)
            ->setReturn($firstProcess)
            ->exec();

        $extendProtocol = new ExtendProtocol();

        $controller = Phantom::m()
            ->setMethod('_initialize')
            ->setArgs($container, $extendProtocol, $request)
            ->setReturn(null)
            ->exec();

        $controller
            ->setMethod('before')
            ->setArgs()
            ->setReturn(null)
            ->exec();

        $controller
            ->setMethod('_doMethod')
            ->setArgs()
            ->setReturn(null)
            ->exec();

        $controller
            ->setMethod('after')
            ->setArgs()
            ->setReturn(null)
            ->exec();

        $controller
            ->setMethod('_checkNeedTemplate')
            ->setArgs()
            ->setReturn(null)
            ->exec();

        $controller
            ->setMethod('_responseJsonWhenUseJsonResponse')
            ->setArgs()
            ->setReturn(null)
            ->exec();

        $container
            ->setMethod('get')
            ->setArgs($namespace)
            ->setReturn($controller)
            ->exec();

        $dispatcher = Phantom::m('System\Core\Route\Dispatcher');
        $dispatcher->container      = $container;
        $dispatcher->extendProtocol = $extendProtocol;
        $dispatcher->request        = $request;

        $this->compareValue(null, $dispatcher->start(), '正常');
    }
}

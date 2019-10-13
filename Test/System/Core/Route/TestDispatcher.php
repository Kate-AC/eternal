<?php

/**
 * Dispatcherのテスト
 */

namespace Test\System\Core\Route;

use System\Core\Di\Container;
use System\Core\Extend\ExtendProtocol;
use System\Core\Route\Request;
use System\Core\Route\Dispatcher;
use System\Log\SystemErrorLogger;
use Test\Mock;
use Test\TestHelper;

class TestDispatcher extends TestHelper
{
    /**
     * __construct
     */
    public function __constructTest()
    {
        $this->compareInstance(
            'System\Core\Route\Dispatcher',
            new Dispatcher($this->container, new ExtendProtocol(), new Request(), new SystemErrorLogger)
        );
    }

    /**
     * start
     */
    public function startTest()
    {
        $firstProcess = Mock::m()
            ->_setMethod('execute')
            ->_setArgs()
            ->_setReturn(null)
            ->e();

        $namespace = 'Test\Hoge\Fuga';

        $request = Mock::m('System\Core\Route\Request')
            ->_setMethod('getControllerNameSpace')
            ->_setArgs()
            ->_setReturn($namespace)
            ->e();

        $container = Mock::m('System\Core\Di\Container')
            ->_setMethod('get')
            ->_setArgs(FIRST_PROCESS_CLASS)
            ->_setReturn($firstProcess)
            ->e();

        $extendProtocol = new ExtendProtocol();

        $controller = Mock::m()
            ->_setMethod('_initialize')
            ->_setArgs($container, $extendProtocol, $request)
            ->_setReturn(null)
            ->e();

        $controller->_setMethod('before')
            ->_setArgs()
            ->_setReturn(null)
            ->e();

        $controller->_setMethod('_doMethod')
            ->_setArgs()
            ->_setReturn(null)
            ->e();

        $controller->_setMethod('after')
            ->_setArgs()
            ->_setReturn(null)
            ->e();

        $controller->_setMethod('_checkNeedTemplate')
            ->_setArgs()
            ->_setReturn(null)
            ->e();

        $controller->_setMethod('_responseJsonWhenUseJsonResponse')
            ->_setArgs()
            ->_setReturn(null)
            ->e();

        $container->_setMethod('get')
            ->_setArgs($namespace)
            ->_setReturn($controller)
            ->e();

        $dispatcher = Mock::m('System\Core\Route\Dispatcher');
        $dispatcher->container      = $container;
        $dispatcher->extendProtocol = $extendProtocol;
        $dispatcher->request        = $request;

        $this->compareValue(null, $dispatcher->start(), '正常');
    }
}

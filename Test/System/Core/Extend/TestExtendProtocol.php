<?php

/**
 * ExtendProtocolのテスト
 */

namespace Test\System\Core\Extend;

use Phantom\Phantom;
use Test\TestHelper;
use System\Core\AutoLoader;
use System\Core\Extend\ExtendProtocol;
use System\Util\FilePathSearcher;
use System\Util\StringOperator;
use System\Exception\SystemException;

use Test\TestModule;

class TestExtendProtocol extends TestHelper
{
    /**
     * start
     */
    public function startTest()
    {
        $extendProtocol = new ExtendProtocol();
        $this->compareValue($extendProtocol, $extendProtocol->start());
    }

    /**
     * end
     */
    public function endTest()
    {
        $extendProtocol = new ExtendProtocol();
        $this->compareValue(null, $extendProtocol->end());
    }

    /**
     * stream_stat
     */
    public function stream_statTest()
    {
        $extendProtocol = Phantom::m('System\Core\Extend\ExtendProtocol');
        $extendProtocol->status = 'status';
        $this->compareValue('status', $extendProtocol->stream_stat());
    }

    /**
     * stream_read
     */
    public function stream_readTest()
    {
        $extendProtocol = Phantom::m('System\Core\Extend\ExtendProtocol');
        $extendProtocol->data     = '1234567890';
        $extendProtocol->position = 0;

        $this->compareValue('123', $extendProtocol->stream_read(3), '読み込んだ分');
        $this->compareValue(3, $extendProtocol->position, 'ポジション');
    }

    /**
     * stream_eof
     */
    public function stream_eofTest()
    {
        $extendProtocol = Phantom::m('System\Core\Extend\ExtendProtocol');
        $extendProtocol->data     = '1234567890';
        $extendProtocol->position = 10;

        $this->compareValue(true, $extendProtocol->stream_eof(), '終点の場合');

        $extendProtocol->position = 2;
        $this->compareValue(false, $extendProtocol->stream_eof(), '終点ではない場合');
    }

    /**
     * stream_open
     * setModule
     */
    public function stream_openAndSetModuleTest()
    {
        $extendProtocol = Phantom::m('System\Core\Extend\ExtendProtocol');
        $this->compareValue($extendProtocol->getOrigin(), $extendProtocol->setModule(new TestModule()), 'setModule');
        $this->compareValue(true, $extendProtocol->stream_open('path'), 'stream_open');
    }
}

namespace Test;

class TestModule
{
    public static function getName()
    {
        return 'TestModule';
    }

    public function run($path, $data)
    {
        return 'data';
    }
}

namespace System\Core\Extend;

/**
 * stream_get_wrappersのオーバーライド
 */
function stream_get_wrappers()
{
    return ['extend'];
}

/**
 * stream_wrapper_registerのオーバーライド
 */
function stream_wrapper_register()
{
    return null;
}

/**
 * stream_wrapper_unregisterのオーバーライド
 */
function stream_wrapper_unregister()
{
    return null;
}

/**
 * statのオーバーライド
 */
function stat($value)
{
    return 'status';
}

/**
 * file_get_contentsのオーバーライド
 */
function file_get_contents($value)
{
    return 'data';
}

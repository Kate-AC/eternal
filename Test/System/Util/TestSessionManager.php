<?php

/**
 * SessionManagerのテスト
 */

namespace Test\System\Util;

use System\Util\SessionManager;
use Test\Mock;
use Test\TestHelper;

class TestSessionManager extends TestHelper
{
    /**
     * set
     * get
     * clear
     * clearAll
     */
    public function setAndGetAndClearAndClearAllTest()
    {
        $sessionManager = new SessionManager();
        $sessionManager->set('hoge', 10);
        $this->compareValue(10, $sessionManager->get('hoge'), 'セッションに値がある場合');
        $this->compareValue(null, $sessionManager->get('fuga'), 'セッションに値がない場合');

        $sessionManager->clear('hoge');
        $this->compareValue(null, $sessionManager->get('hoge'), 'セッションの値をクリアした場合');

        $sessionManager->set('hoge', 100);
        $sessionManager->set('fuga', 200);
        $sessionManager->clearAll();
        $this->compareValue(null, $sessionManager->get('hoge'), 'セッションの値を全てクリアした場合');
    }
}

namespace System\Util;

/**
 * session_startのオーバーライド
 */
function session_start()
{
}

/**
 * session_write_closeのオーバーライド
 */
function session_write_close()
{
}

/**
 * setcookieのオーバーライド
 */
function setcookie()
{
}

/**
 * session_nameのオーバーライド
 */
function session_name()
{
}

/**
 * timeのオーバーライド
 */
function time()
{
}

/**
 * session_destroyのオーバーライド
 */
function session_destroy()
{
}

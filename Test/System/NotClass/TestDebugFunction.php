<?php

/**
 * DebugFunctionのテスト
 */

namespace Test\System\NotClass;

use Test\Mock;
use Test\TestHelper;

class TestDebugFunction extends TestHelper
{
    /**
     * v
     */
    public function vTest()
    {
        $message = 'hoge';
        ob_start();
        v($message);
        $result = ob_get_contents();
        ob_end_clean();

        preg_match(sprintf('/\"(%s)\"/', $message), $result, $match);

        $this->compareValueLax($message, $match[1]);
    }

    /**
     * ve
     */
    public function veTest()
    {
        $this->compareValue(true, function_exists('ve'), 'exitが呼ばれるためメソッドの存在のみ確認');
    }

    /**
     * mem
     */
    public function memTest()
    {
        $this->compareValue(true, function_exists('mem'), 'exitが呼ばれるためメソッドの存在のみ確認');
    }
}

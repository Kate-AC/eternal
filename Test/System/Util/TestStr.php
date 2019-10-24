<?php

/**
 * Strのテスト
 */

namespace Test\System\Util;

use System\Util\Str;
use Phantom\Phantom;
use Test\TestHelper;

class TestStr extends TestHelper
{
    /**
     * snakeToCamel
     */
    public function snakeToCamelTest()
    {
        $this->compareValue('hogeFugaPiyo', Str::snakeToCamel('hoge_fuga_piyo'));
    }

    /**
     * camelToSnake
     */
    public function camelToSnakeTest()
    {
        $this->compareValue('hoge_fuga_piyo', Str::camelToSnake('hogeFugaPiyo'));
    }

    /**
     * columnToGetter
     */
    public function columnToGetterTest()
    {
        $this->compareValue('getHogeFuga', Str::columnToGetter('hoge_fuga'));
    }

    /**
     * columnToSetter
     */
    public function columnToSetterTest()
    {
        $this->compareValue('setHogeFuga', Str::columnToSetter('hoge_fuga'));
    }
}


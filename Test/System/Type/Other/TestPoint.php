<?php

/**
 * Pointのテスト
 */

namespace Test\System\Type\Other;

use System\Type\Other\Point;
use System\Exception\IncorrectTypeException;
use Test\Mock;
use Test\TestHelper;

class TestPoint extends TestHelper
{
    /**
     * __construct
     * getLat
     * getLng
     */
    public function __constructAndGetLatAndGetLngTest()
    {
        $lat   = 1.1;
        $lng   = 2.2;
        $point = new Point($lat, $lng);

        $this->compareInstance('System\Type\Other\Point', $point, 'インスタンスの生成');
        $this->compareInstance('System\Type\Other\Point', new Point('POINT(1.1 2.2)'), 'インスタンスの生成(文字列から)');

        $this->compareValue($lat, $point->getLat(), '緯度');
        $this->compareValue($lng, $point->getLng(), '経度');

        try {
            new Point('hoge');
            $this->throwError('例外が発生すべき場所で発生していない');
        } catch (IncorrectTypeException $e) {
            $this->compareException('POINT文字列の形式が正しくありません', $e, '正しくないフォーマットを渡した場合');
        }
    }
}

<?php

/**
 * Kitのテスト
 */

namespace Test\System\Util;

use System\Util\Kit;
use Phantom\Phantom;
use Test\TestHelper;

class TestKit extends TestHelper
{
    /**
     * getRandomName
     */
    public function getRandomNameTest()
    {
        $this->compareValue('1111111111_abcdefghij', Kit::getRandomName());
    }

    /**
     * pathToNameSpace
     */
    public function pathToNameSpaceTest()
    {
        $this->compareValue('App\Hoge', Kit::pathToNameSpace(CURRENT_DIR . 'App/Hoge.php'), 'App');
        $this->compareValue('System\Hoge', Kit::pathToNameSpace(CURRENT_DIR . 'System/Hoge.php'), 'System');
        $this->compareValue('Test\System\Hoge', Kit::pathToNameSpace(CURRENT_DIR . 'Test/System/Hoge.php'), 'Test');
    }

    /**
     * nameSpaceToPath
     */
    public function nameSpaceToPathTest()
    {
        $this->compareValue(SRC_DIR . 'App/Hoge.php', Kit::nameSpaceToPath('App\Hoge'), 'App');
        $this->compareValue(CURRENT_DIR . 'System/Hoge.php', Kit::nameSpaceToPath('System\Hoge'), 'System');
        $this->compareValue(CURRENT_DIR . 'Test/System/Hoge.php', Kit::nameSpaceToPath('Test\System\Hoge'), 'Test');
    }

    /**
     * getDocCommentByModelProperty
     */
    public function getDocCommentByModelPropertyTest()
    {
        $docComment = <<<EOD
/**
 * @model int
 */
EOD;
        $this->compareValue('int', Kit::getDocCommentByModelProperty($docComment), 'Docコメントが存在する場合');

        $docComment = 'aaaa';
        $this->compareValue(null, Kit::getDocCommentByModelProperty($docComment), 'Docコメントが存在しない場合');
    }

    /**
     * autoConvert
     */
    public function autoConvertTest()
    {
        $this->compareValue(true, is_int(Kit::autoConvert('111')), 'int');
        $this->compareValue(true, is_float(Kit::autoConvert('111.1')), 'float');
        $this->compareValue(true, is_string(Kit::autoConvert('aaaa')), 'string');
    }

 
}

namespace System\Util;

function microtime($boolean)
{
    return 1111111111.111;
}

function str_shuffle()
{
    return 'abcdefghij';
}

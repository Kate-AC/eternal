<?php

/**
 * StringOperatorのテスト
 */

namespace Test\System\Util;

use System\Util\StringOperator;
use Test\Mock;
use Test\TestHelper;

class TestStringOperator extends TestHelper
{
    /**
     * getRandomName
     */
    public function getRandomNameTest()
    {
        $this->compareValue('1111111111_abcdefghij', StringOperator::getRandomName());
    }

    /**
     * snakeToCamel
     */
    public function snakeToCamelTest()
    {
        $this->compareValue('hogeFugaPiyo', StringOperator::snakeToCamel('hoge_fuga_piyo'));
    }

    /**
     * camelToSnake
     */
    public function camelToSnakeTest()
    {
        $this->compareValue('hoge_fuga_piyo', StringOperator::camelToSnake('hogeFugaPiyo'));
    }

    /**
     * columnToGetter
     */
    public function columnToGetterTest()
    {
        $this->compareValue('getHogeFuga', StringOperator::columnToGetter('hoge_fuga'));
    }

    /**
     * columnToSetter
     */
    public function columnToSetterTest()
    {
        $this->compareValue('setHogeFuga', StringOperator::columnToSetter('hoge_fuga'));
    }

    /**
     * pathToNameSpace
     */
    public function pathToNameSpaceTest()
    {
        $this->compareValue('App\Hoge', StringOperator::pathToNameSpace(PUBLIC_DIR . 'App/Hoge.php'), 'App');
        $this->compareValue('System\Hoge', StringOperator::pathToNameSpace(SOURCE_DIR . 'System/Hoge.php'), 'System');
        $this->compareValue('Test\System\Hoge', StringOperator::pathToNameSpace(SOURCE_DIR . 'Test/System/Hoge.php'), 'Test');
    }

    /**
     * nameSpaceToPath
     */
    public function nameSpaceToPathTest()
    {
        $this->compareValue(PUBLIC_DIR . 'App/Hoge.php', StringOperator::nameSpaceToPath('App\Hoge'), 'App');
        $this->compareValue(SOURCE_DIR . 'System/Hoge.php', StringOperator::nameSpaceToPath('System\Hoge'), 'System');
        $this->compareValue(SOURCE_DIR . 'Test/System/Hoge.php', StringOperator::nameSpaceToPath('Test\System\Hoge'), 'Test');
    }

    /**
     * tableToClassName
     */
    public function tableToClassNameTest()
    {
        $tablePrefixList = getTablePrefix();
        foreach ($tablePrefixList as $tablePrefix) {
            $this->compareValue('Hoge', StringOperator::tableToClassName($tablePrefix . '_hoge'), $tablePrefix);
        }
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
        $this->compareValue('int', StringOperator::getDocCommentByModelProperty($docComment), 'Docコメントが存在する場合');

        $docComment = 'aaaa';
        $this->compareValue(null, StringOperator::getDocCommentByModelProperty($docComment), 'Docコメントが存在しない場合');
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

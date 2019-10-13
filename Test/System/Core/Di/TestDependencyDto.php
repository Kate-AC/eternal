<?php

/**
 * DependencyDtoのテスト
 */

namespace Test\System\Core\Di;

use Test\Mock;
use Test\Parse;
use Test\TestHelper;
use System\Core\Di\DependencyDto;

class TestDependencyDto extends TestHelper
{
    /**
     * __construct
     */
    public function __constructTest()
    {
        $this->compareInstance('System\Core\Di\DependencyDto', new DependencyDto('hoge', []));
    }

    /**
     * getClassNameSpace
     */
    public function getClassNameSpaceTest()
    {
        $classNameSpace      = 'hoge';
        $dependencyClassList = ['fuga', 'piyo'];
        $dependencyDto       = new DependencyDto($classNameSpace, $dependencyClassList);
        $this->compareValue($classNameSpace, $dependencyDto->getClassNameSpace());
    }

    /**
     * getDependentClassList
     */
    public function getDependencyClassListTest()
    {
        $classNameSpace      = 'hoge';
        $dependencyClassList = ['fuga', 'piyo'];
        $dependencyDto       = new DependencyDto($classNameSpace, $dependencyClassList);
        $this->compareValue($dependencyClassList, $dependencyDto->getDependencyClassList());
    }
}

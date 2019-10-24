<?php

/**
 * FilePathSearcherのテスト
 */

namespace Test\System\Util;

use System\Util\FilePathSearcher;
use Phantom\Phantom;
use Test\TestHelper;

class TestFilePathSearcher extends TestHelper
{
    /**
     * setUseDir
     * setUnUseDir
     */
    public function setUseDirAndSetUnUseDirTest()
    {
        $filePathSearcher = Phantom::m('System\Util\FilePathSearcher');
        $useDirList = ['hoge', 'fuga'];
        $filePathSearcher->setUseDir($useDirList);
        $this->compareValue($useDirList, $filePathSearcher->useDirList, '使用するディレクトリ');

        $unUseDirList = ['piyo', 'mosu'];
        $filePathSearcher->setUnUseDir($unUseDirList);
        $this->compareValue(array_merge($unUseDirList, ['.', '..']), $filePathSearcher->unUseDirList, '使用しないディレクトリ');
    }

    /**
     * search
     * getRecursive
     */
    public function searchAndGetRecursiveTest()
    {
        $filePathSearcher = new FilePathSearcher();
        $allFilePathList = $filePathSearcher
            ->setUseDir([TEST_DIR . 'System/Util/'])
            ->search();
        $this->compareValue($filePathSearcher->getAllFilePathList(), $allFilePathList);
    }

    /**
     * getAllFilePathList
     */
    public function getAllFilePathListTest()
    {
        $filePathSearcher = Phantom::m('System\Util\FilePathSearcher');
        $filePathSearcher->allFilePathList = [];
        $filePathSearcher->setMethod('search')
            ->setArgs()
            ->setReturn(true)
            ->exec();

        $this->compareValue(true, $filePathSearcher->getAllFilePathList(), 'ファイルパスの配列が空の場合');

        $allFilePathList = ['hoge' => 'hoge'];
        $filePathSearcher->allFilePathList = $allFilePathList;
        $this->compareValue($allFilePathList, $filePathSearcher->getAllFilePathList(), 'ファイルパスの配列が空ではない場合');
    }
}


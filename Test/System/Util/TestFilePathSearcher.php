<?php

/**
 * FilePathSearcherのテスト
 */

namespace Test\System\Util
{

use System\Util\FilePathSearcher;
use Test\Mock;
use Test\TestHelper;

class TestFilePathSearcher extends TestHelper
{
	/**
	 * setUseDir
	 * setUnUseDir
	 */
	public function setUseDirAndSetUnUseDirTest()
	{
		$filePathSearcher = Mock::m('System\Util\FilePathSearcher');
		$useDirList = ['hoge', 'fuga'];
		$filePathSearcher->setUseDir($useDirList);
		$this->compareValue($useDirList, $filePathSearcher->useDirList, '使用するディレクトリ');

		$unUseDirList = ['piyo', 'mosu'];
		$filePathSearcher->setUnUseDir($unUseDirList);
		$this->compareValue($unUseDirList, $filePathSearcher->unUseDirList, '使用しないディレクトリ');
	}

	/**
	 * search
	 * getRecursive
	 */
	public function searchAndGetRecursiveTest()
	{
		$filePathSearcher = Mock::m('System\Util\FilePathSearcher');
		$useDirList = ['hoge/', 'fuga/', 'piyo/'];

		$filePathSearcher->setUseDir($useDirList);
		$filePathSearcher->setUnUseDir(['hoge/']);

		$allFilePathList = [
			'fuga/fugaDir/fugaFile' => 'fuga/fugaDir/fugaFile',
			'piyo/piyoFile'         => 'piyo/piyoFile'
		];

		$this->compareValue($allFilePathList, $filePathSearcher->search());
	}

	/**
	 * getAllFilePathList
	 */
	public function getAllFilePathListTest()
	{
		$filePathSearcher = Mock::m('System\Util\FilePathSearcher');
		$filePathSearcher->allFilePathList = [];
		$filePathSearcher->_setMethod('search')
			->_setArgs()
			->_setReturn(true)
			->e();
		$this->compareValue(true, $filePathSearcher->getAllFilePathList(), 'ファイルパスの配列が空の場合');

		$allFilePathList = ['hoge' => 'hoge'];
		$filePathSearcher->allFilePathList = $allFilePathList;
		$this->compareValue($allFilePathList, $filePathSearcher->getAllFilePathList(), 'ファイルパスの配列が空ではない場合');
	}
}
}

namespace System\Util
{

	$GLOBALS['hoge']     = 0;
	$GLOBALS['fuga']     = 0;
	$GLOBALS['piyo']     = 0;
	$GLOBALS['fugaFile'] = 0;

	/**
	 * opendirのオーバーライド
	 */
	function opendir($value)
	{
		return $value;
	}

	/**
	 * closedirのオーバーライド
	 */
	function closedir($value)
	{
		return null;
	}

	/**
	 * readdirのオーバーライド
	 */
	function readdir($value)
	{
		switch ($value)
		{
			case 'hoge/':
				if (0 === $GLOBALS['hoge']) {
					$GLOBALS['hoge']++;
					return 'hoge/';
				} else {
					return false;
				}
			case 'fuga/':
				if (0 === $GLOBALS['fuga']) {
					$GLOBALS['fuga']++;
					return 'fugaDir';
				} else {
					return false;
				}
			case 'piyo/':
				if (0 === $GLOBALS['piyo']) {
					$GLOBALS['piyo']++;
					return 'piyoFile';
				} else {
					return false;
				}
			//2週目
			case 'fuga/fugaDir/':
				if (0 === $GLOBALS['fugaFile']) {
					$GLOBALS['fugaFile']++;
					return 'fugaFile';
				} else {
					return false;
				}
		}
	}

	/**
	 * is_dirのオーバーライド
	 */
	function is_dir($value)
	{
		switch ($value)
		{
			case 'fuga/fugaDir':
				return true;
			case 'piyo/piyoFile':
				return false;
			case 'fuga/fugaDir/fugaFile':
				return false;
		}
	}
}


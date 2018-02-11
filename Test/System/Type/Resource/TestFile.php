<?php

/**
 *  * Fileのテスト
 *   */

namespace Test\System\Type\Resource;

use System\Type\Resource\File;
use System\Exception\SystemException;
use Test\Mock;
use Test\TestHelper;

class TestFile extends TestHelper
{
	/**
	 * __construct
	 * getName
	 * getSize
	 * save
	 */
	public function __constructAndGetNameAndGetSizeAndSaveTest()
	{
		$file = new File(__FILE__, 'hoge');
		$this->compareInstance('System\Type\Resource\File', $file, 'インスタンス生成');
		$this->compareValue('hoge', $file->getName(), '名前の取得');
		$this->compareValue(true, is_int($file->getSize()), 'サイズの取得');

		$property = new \ReflectionProperty($file, 'uri');
		$property->setAccessible(true);
		$property->setValue($file, 'uri');
		$this->compareValue(null, $file->save('file'), '保存');

		try {
			$file->save();
			$this->throwError('例外が発生すべき箇所で発生していない');
		} catch (SystemException $e) {
			$this->compareException('ファイルの保存先が指定されていません', $e, '例外');
		}
	}
}

namespace System\Type\Resource;

$GLOBALS['feofCount'] = 0;

/**
 * fopenのオーバーライド
 */
function fopen($uri, $type)
{
	if ('uri' === $uri && 'r' === $type) {
		return 'fp';
	}
	throw new \Exception('Test: fopen時のエラー');
}

/**
 * file_put_contentsのオーバーライド
 */
function file_put_contents($path, $byte, $type = null)
{
	if ('file' === $path && '' === $byte) {
		return null;
	}

	if ('file' === $path && 'buffer' === $byte && FILE_APPEND === $type) {
		return null;
	}

	throw new \Exception('Test: file_put_contents時のエラー');
}
/**
 * feofのオーバーライド
 */
function feof($fp)
{
	if ('fp' === $fp && 0 === $GLOBALS['feofCount']) {
		$GLOBALS['feofCount']++;
		return true;
	}
	return false;
}
/**
 * freadのオーバーライド
 */
function fread($fp, $byte)
{
	if ('fp' === $fp && 4096 === $byte) {
		return 'buffer';
	}
	throw new \Exception('Test: fread時のエラー');
}
/**
 * fcloseのオーバーライド
 */
function fclose($fp)
{
	if ('fp' === $fp) {
		return null;
	}
	throw new \Exception('Test: fclose時のエラー');
}


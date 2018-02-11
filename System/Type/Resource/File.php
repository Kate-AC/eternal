<?php

/**
 * ファイルクラス
 */

namespace System\Type\Resource;

use System\Exception\SystemException;

class File
{
	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var int
	 */
	private $size;

	/**
	 * @var string
	 */
	private $uri;

	/**
	 * コンストラクタ
	 *
	 * @param string $uri
	 * @param string $name
	 */
	public function __construct($uri = null, $name = null)
	{
		$this->name = $name;
		$this->uri  = $uri;
		if (!is_null($uri)) {
			$this->size = filesize($uri);
		}
	}

	/**
	 * 名前を取得する
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * サイズを取得する
	 *
	 * @return int
	 */
	public function getSize()
	{
		return $this->size;
	}

	/**
	 * ファイルを保存する
	 *
	 * @param string $path
	 * @throw SystemException
	 */
	public function save($path = null)
	{
		if (is_null($path)) {
			throw new SystemException('ファイルの保存先が指定されていません');
		}

		$fp = fopen($this->uri, "r");
		if (false !== $fp) {
			file_put_contents($path, "");
			while (!feof($fp)) {
				$buffer = fread($fp, 4096);
				if (false !== $buffer) {
					file_put_contents($path, $buffer, FILE_APPEND);
				}
			}
			fclose($fp);
		}
	}
}


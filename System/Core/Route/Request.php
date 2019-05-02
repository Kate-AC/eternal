<?php

/**
 * リクエスト情報クラス
 */

namespace System\Core\Route;

use System\Type\Resource\File;
use System\Type\Resource\Image;
use System\Util\StringOperator;

class Request
{
	/**
	 * @var string[]
	 */
	private $server;

	/**
	 * @var string[]
	 */
	private $post;

	/**
	 * @var string[]
	 */
	private $get;

	/**
	 * @var mixed[]
	 */
	private $files;

	/**
	 * コンストラクタ
	 */
	public function __construct()
	{
		$this->server = $_SERVER;
		$this->post   = $_POST;
		$this->get    = $_GET;
		$this->files  = $_FILES;
	}

	/**
	 * get値を取得する
	 *
	 * @param string $value
	 * @param string $default
	 * @return string
	 */
	public function get($value = null, $default = null)
	{
		$getList = $this->get;
		if (is_null($value) && is_null($default)) {
			return $getList;
		}

		if (isset($getList[$value])) {
			return $getList[$value];
		}
		return $default;
	}

	/**
	 * post値を取得する
	 *
	 * @param string $value
	 * @param string $default
	 * @return string
	 */
	public function post($value = null, $default = null)
	{
		$postList = $this->post;

		if (is_null($value) && is_null($default)) {
			return $postList;
		}

		if (isset($postList[$value])) {
			return $postList[$value];
		}
		return $default;
	}

	/**
	 * file値を取得する
	 *
	 * @param string $value
	 * @param string $default
	 * @return mixed
	 */
	public function file($value = null, $default = null)
	{
		$fileList = [];
		foreach ($this->files as $i => $file) {
      if (0 < mb_strlen($file['tmp_name'])) {
			  $mimeType = $this->getMimeType($file['tmp_name']);
      } else {
			  $mimeType = null;
      }

			switch ($mimeType) {
				case 'image/png':
				case 'image/jpeg':
				case 'image/gif':
				case 'image/bmp':
					$fileList[$i] = new Image($file['tmp_name'], $file['name']);
					break;
				default:
					$fileList[$i] = new File($file['tmp_name'], $file['name']);
					break;
			}
		}

		if (is_null($value) && is_null($default)) {
			return $fileList;
		}

		if (isset($fileList[$value])) {
			return $fileList[$value];
		}
		return $default;
	}

	/**
	 * MIMETYPEを取得する
	 *
	 * @param string $filePath
	 * @return string
	 */
	private function getMimeType($filePath)
	{
		return (new \finfo(FILEINFO_MIME_TYPE))->file($filePath);
	}

	/**
	 * server変数の値を取得する
	 *
	 * @param string $value
	 * @param string $default
	 * @return string
	 */
	public function server($value = null, $default = null)
	{
		$serverList = $this->server;
		if (is_null($value) && is_null($default)) {
			return $serverList;
		}

		if (isset($serverList[$value])) {
			return $serverList[$value];
		}
		return $default;
	}

	/**
	 * JsonObjectを取得する(CurlでPOSTされた場合等)
	 *
	 * @return string[]
	 */
	public function json()
	{
		$jsonString = file_get_contents('php://input');
		if (false === ($jsonArray = json_decode($jsonString, true))) {
			return null;
		}
		return $jsonArray;
	}

	/**
	 * 表示するコントローラの名前空間を返す
	 *
	 * @return string 
	 */
	public function getControllerNameSpace()
	{
		$uriList = $this->getUri();
		$nameSpace = [];
		foreach ($uriList as $key => $value) {
			if ($key !== count($uriList) - 1) {
				$nameSpace[] = ucfirst(StringOperator::snakeToCamel($value));
			}
		}

		return StringOperator::pathToNameSpace(CONTROLLER_DIR . implode('/', $nameSpace) . 'Controller');
	}

	/**
	 * 表示するアクションを返す
	 *
	 * @return string 
	 */
	public function getControllerMethod()
	{
		$uriList = $this->getUri();
		return end($uriList) . 'Action';
	}

	/**
	 * アクセスしてきたURLの末尾のスラッシュやindexの省略を考慮したURLを返す
	 *
	 * @return string[]
	 */
	public function getUri()
	{
		$uri = ltrim($this->server['REQUEST_URI'], '/');
		$uri = str_replace(CURRENT_DIR, '', $uri);

		if (false !== strstr($uri, '?', true)) {
			$uri = strstr($uri, '?', true);
		}

		$uriList = explode('/', $uri);
		$last    = count($uriList) - 1;
		$uriList[$last] = '' !== $uriList[$last] ? $uriList[$last] : 'index';

		if (1 === count($uriList)) {
			$uriList = array_merge(['index'], $uriList);
		}

		return $uriList;
	}
}

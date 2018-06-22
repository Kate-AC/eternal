<?php

/**
 * ファイルパスを再帰的に読み込むクラス
 */

namespace System\Util;

class FilePathSearcher
{
	/**
	 * @var string[]
	 */
	private $useDirList = [];

	/**
	 * @var string[]
	 */
	private $unUseDirList = [];

	/**
	 * @var string[]
	 */ 
	private $allFilePathList = [];

	/**
	 * 読み込むディレクトリをセットする
	 *
	 * @param string[] $array
	 * @return self
	 */
	public function setUseDir(array $array = [])
	{
		$this->useDirList = $array;

		return $this;
	}

	/**
	 * 読み飛ばすディレクトリをセットする
	 *
	 * @param string[] $array
	 * @return self
	 */
	public function setUnUseDir(array $array = [])
	{
		$this->unUseDirList = $array;

		return $this;
	}

	/**
	 * ディレクトリのパスからファイルパスのリストを取得
	 *
	 * @return string[]
	 */
	public function search()
	{
		foreach ($this->useDirList as $parentDir) {
			$this->getRecursive($parentDir);
		}

		return $this->allFilePathList;
	}

	/**
	 * ファイルパスを再帰的に取得する
	 *
	 * @param string $parentDir
	 */
	private function getRecursive($parentDir) {
		if (false !== ($openDir = opendir($parentDir))) {
			while (false !== ($readFile = readdir($openDir))) {
				if (in_array($readFile, $this->unUseDirList)) {
					continue;
				}
				$fullPath = $parentDir . $readFile;

				if (is_dir($fullPath)) {
					$this->getRecursive($fullPath . '/');
				} else {
					//notice回避
					$allFilePathList = $this->allFilePathList;
					$allFilePathList[$fullPath] = $fullPath;
					$this->allFilePathList = $allFilePathList;
				}
			}
			closedir($openDir);
		}
	}

	/**
	 * 取得したファイルパスのリストを返す
	 *
	 * @return string[]
	 */
	public function getAllFilePathList()
	{
		//Closure使用時に$this->allFilePathListの中身が入っていても
		//empty()がtrue判定を返すので要素数と比較している
		if (0 === count($this->allFilePathList)) {
			return $this->search();
		}
		return $this->allFilePathList;
	}
}

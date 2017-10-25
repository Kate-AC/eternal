<?php

/**
 * クラスが依存しているクラスを全て取得する
 */

namespace System\Core\Di;

use System\Exception\SystemException;
use System\Util\FilePathSearcher;
use System\Util\StringOperator;

class DependencySearcher
{
	/**
	 * @var DependencyDto[]
	 */
	private $dependencyDtoList = [];

	/**
	 * @var FilePathSearcher
	 */
	private $filePathSearcher;

	/**
	 * コンストラクタ
	 *
	 * @var FilePathSearcher $filePathSearcher
	 */
	public function __construct(
		FilePathSearcher $filePathSearcher
	) {
		$this->filePathSearcher = $filePathSearcher;
	}

	/**
	 * 依存しているクラスの一覧を取得
	 *
	 * @param string $nameSpace
	 * @return DependencyDto[]
	 */
	public function search($nameSpace)
	{
		$this->getRecursive($nameSpace);
		return $this->dependencyDtoList;
	}

	/**
	 * 名前空間から依存関係を再帰的に取得
	 *
	 * @param string $nameSpace
	 */
	private function getRecursive($nameSpace)
	{
		$reflectionClass = new \ReflectionClass($nameSpace);

		$dependencyArgList = [];
		if (true === $reflectionClass->hasMethod('__construct')) {
			$argList = (new \ReflectionMethod($nameSpace, '__construct'))->getParameters();

			foreach ($argList as $arg) {
				if (is_null($arg->getClass())) {
					continue;
				}

				$dependencyNameSpace = $arg->getClass()->name;
				if (!is_null($dependencyNameSpace)) {
					$dependencyArgList[] = $dependencyNameSpace;
				}
			}
		}

		$this->dependencyDtoList[$reflectionClass->getName()] = new DependencyDto(
			$reflectionClass->getName(),
			$dependencyArgList
		);

		foreach ($dependencyArgList as $arg) {
			$this->getRecursive($arg);
		}
	}

	/**
	 * テーブル名から依存関係を再帰的に取得
	 *
	 * @param string $table
	 * @return string
	 */
	public function searchByTable($table)
	{
		$filePathList = $this->filePathSearcher
			->setUseDir([MODEL_DIR])
			->setUnUseDir(getUnUseDirs())
			->search();

		foreach ($filePathList as $filePath) {
			$list = explode('/', $filePath);
			if (str_replace('.php', '', end($list)) === StringOperator::tableToClassName($table)) {
				$namespace = StringOperator::pathToNameSpace($filePath);
				$this->search($namespace);
				return $namespace;
			}
		}
		throw new SystemException(sprintf('テーブル名(%s)に対応するモデルが存在しません', $table));
	}

	/**
	 * 取得した依存関係を取得する
	 *
	 * @return DependencyDto[]
	 */
	public function getDependencyDtoList()
	{
		return $this->dependencyDtoList;
	}
}


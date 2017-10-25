<?php

/**
 * 依存関係を解決させた状態のクラスを取得
 */

namespace System\Core\Di;

use System\Core\Cache;
use System\Exception\DiException;

class DependencyInjector
{
	/**
	 * @var Cache
	 */
	private $cache;

	/**
	 * @var object[]
	 */
	private static $resolvedClassList = [];

	/**
	 * コンストラクタ
	 *
	 * @param Cache $cache
	 */
	public function __construct(Cache $cache) {
		$this->cache = $cache;
	}

	/**
	 * クラスを生成する
	 *
	 * @param DependencyDto[] $dependencyDtoList
	 * @return DependencyInjector
	 */
	public function create(array $dependencyDtoList)
	{
		$this->createRecursive($dependencyDtoList);
		return $this;
	}

	/**
	 * 依存関係を解決したクラスを生成していく
	 *
	 * @param string[] $needClassList
	 */
	private function createRecursive(array $dependencyDtoList)
	{
		foreach ($dependencyDtoList as $dependencyDto) {
			$dependencyClassList = $dependencyDto->getDependencyClassList();
			$classNameSpace      = $dependencyDto->getClassNameSpace();

			//依存するクラスがない場合はインスタンスを生成してスキップする
			if (empty($dependencyClassList)) {
				$newClass = new $classNameSpace;
				self::$resolvedClassList[$classNameSpace] = $newClass;

				$this->cache->setCache(get_class($newClass), $newClass);
				unset($dependencyDtoList[$classNameSpace]);
				continue;
			}

			//依存するクラスのインスタンスが全て生成されていることを確認してインスタンスを生成する
			if (true === $this->checkFlushDependency($dependencyClassList)) {
				$argList = [];
				foreach ($dependencyClassList as $dependencyClass) { 
					$argList[] = self::$resolvedClassList[$dependencyClass];
				}

				$reflectionClass = new \ReflectionClass($classNameSpace);
				$newClass        = $reflectionClass->newInstanceArgs($argList);

				self::$resolvedClassList[$classNameSpace] = $newClass;
				$this->cache->setCache(get_class($newClass), $newClass);
				unset($dependencyDtoList[$classNameSpace]);
			}
		}

		if (1 <= count($dependencyDtoList)) {
			$this->createRecursive($dependencyDtoList);
		}
	}

	/**
	 * 必要なクラスがすべてそろっているか調べる
	 *
	 * @param string[] $dependencyClassList
	 * @return boolean
	 */
	private function checkFlushDependency(array $dependencyClassList)
	{
		$resolvedClassList = array_keys(self::$resolvedClassList);

		foreach ($dependencyClassList as $key => $dependencyClass) {
			if (in_array($dependencyClass, $resolvedClassList, true)) {
				unset($dependencyClassList[$key]);
			}
		}

		if (empty($dependencyClassList)) {
			return true;
		}
		return false;
	}

	/**
	 * 依存を解決したクラス一覧を取得
	 *
	 * @return object[]
	 */
	public function getResolvedClassList()
	{
		return self::$resolvedClassList;
	}

	/**
	 * 依存を解決したクラスを取得
	 *
	 * @param string $nameSpace
	 * @return object
	 */
	public function getResolvedClass($nameSpace)
	{
		if (isset(self::$resolvedClassList[$nameSpace])) {
			return self::$resolvedClassList[$nameSpace];
		}
		throw new DiException(sprintf('存在しない名前空間(%s)を指定した', $nameSpace));
	}
}

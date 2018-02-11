<?php

/**
 * クラスの依存を格納するクラス
 */

namespace System\Core\Di;

class DependencyDto
{
	/**
	 * @var string
	 */
	private $classNameSpace;

	/**
	 * @var string[]
	 */
	private $dependencyClassList;

	/**
	 * コンストラクタ
	 *
	 * @param string   $classNameSpace
	 * @param string[] $dependencyClassList
	 */
	public function __construct($classNameSpace, array $dependencyClassList)
	{
		$this->classNameSpace      = $classNameSpace;
		$this->dependencyClassList = $dependencyClassList;
	}

	/**
	 * 名前空間を含むクラス名を取得
	 *
	 * @return string
	 */
	public function getClassNameSpace() {
		return $this->classNameSpace;
	}

	/**
	 * 依存関係を名前空間で取得
	 *
	 * @return string[]
	 */
	public function getDependencyClassList() {
		return $this->dependencyClassList;
	}
}

<?php

/**
 * SUMやCOUNT等の存在しないカラムをまとめるクラス
 */

namespace System\Database\MySql;

class Collect
{
	/**
	 * @var string[]
	 */
	private $properties = []; 

	/**
	 * コンストラクタ
	 *
	 * @param string[] $properties
	 * @return Collect
	 */
	public function __construct(array $properties)
	{
		foreach ($properties as $property => $value) {
			$this->properties[$property] = $value;
		}
	}

	/**
	 * 動的にセットしたプロパティの値を取得
	 *
	 * @param string $name
	 * @param string $arguments
	 */
	public function __call($name, $arguments)
	{
		if (isset($this->properties[$name])) {
			return $this->properties[$name];
		}
		return null;
	}
}

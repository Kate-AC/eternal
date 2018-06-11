<?php

/**
 * SUMやCOUNT等の存在しないカラムをまとめるクラス
 */

namespace System\Database;

class Collect
{
	/**
	 * @var string[]
	 */
	private $properties = []; 

	/**
	 * @var string[]
	 */
	private $extendProperty = [];

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
	public function __call($name, array $arguments)
	{
		if (isset($this->extendProperty[$name])) {
			$property = $this->extendProperty[$name];
			return $this->autoConvert($this->properties[$property]);
		}

		if (isset($this->properties[$name])) {
			return $this->autoConvert($this->properties[$name]);
		}

		return null;
	}

	/**
	 * 返す値の型を自動で判別する
	 *
	 * @param  string $value
	 * @return mixed
	 */
	private function autoConvert($value)
	{
		if (1 === preg_match('/^-?[0-9]+$/', $value)) {
			return (int)$value;
		}

		if (1 === preg_match('/^-?[0-9]+\.{1}[0-9]+$/', $value)) {
			return (float)$value;
		}

		return $value;
	}

	/**
	 * プロパティの別名をセットする
	 *
	 * @param string $original
	 * @param string $as
	 */
	public function setExtendProperty($original, $as)
	{
		$this->extendProperty[$as] = $original;
	}
}

<?php

/**
 * Floatクラス
 */

namespace System\Type\Primitive;

use System\Exception\IncorrectTypeException;

class Float extends AbstractPrimitive
{
	use CalculateTrait;

	/**
	 * 値チェック
	 *
	 * @param mixed $value
	 * @throw IncorrectTypeException
	 */
	protected function check($value)
	{
		if (true === ($value instanceof Float)) {
			return;
		}

		if (is_null($value)) {
			return;
		}

		if (is_object($value) && false === ($value instanceof Float)) {
			throw new IncorrectTypeException('Float型ではないオブジェクトが渡された');
		}

		if (is_array($value)) {
			throw new IncorrectTypeException('配列が渡された');
		}

		if (is_string($value) || 0 === preg_match('/^-?([0-9]+|[0-9]+\.[0-9]+)$/', $value)) {
			throw new IncorrectTypeException('Floatではない値が渡された');
		}
	}

	/**
	 * 値をフォーマットする
	 *
	 * @param mixced $value
	 * @return float
	 */
	protected function format($value)
	{
		if (is_null($value)) {
			return $value;
		}

		if (true === ($value instanceof Float)) {
			return $value->getValue();
		}

		return (float)$value;
	}

	/**
	 * Boolean型に変換する
	 *
	 * @return Boolean
	 */
	public function toBoolean()
	{
		if (0 == $this->value) {
			return new Boolean(false);
		} elseif (1 == $this->value) {
			return new Boolean(true);
		}

		throw new IncorrectTypeException('Boolean型に変換できる値は0か1のみです');
	}

	/**
	 * Int型に変換する
	 *
	 * @return Int
	 */
	public function toInt()
	{
		return new Int((int)$this->value);
	}

	/**
	 * String型に変換する
	 *
	 * @return String
	 */
	public function toString()
	{
		return new String((string)$this->value);
	}
}


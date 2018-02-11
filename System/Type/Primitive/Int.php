<?php

/**
 * Intクラス
 */

namespace System\Type\Primitive;

use System\Exception\IncorrectTypeException;

class Int extends AbstractPrimitive
{
	use CalculateTrait;

	/**
	 * 値チェック
	 *
	 * @param mixed $value
	 * @throw DiffelentPrimitiveException
	 */
	protected function check($value)
	{
		if (true === ($value instanceof Int)) {
			return;
		}

		if (is_null($value)) {
			return;
		}

		if (is_object($value) && false === ($value instanceof Int)) {
			throw new IncorrectTypeException('Int型ではないオブジェクトが渡された');
		}

		if (is_array($value)) {
			throw new IncorrectTypeException('配列が渡された');
		}

		if (!is_int($value)) {
			throw new IncorrectTypeException('Intではない値が渡された');
		}
	}

	/**
	 * 値をフォーマットする
	 *
	 * @param mixced $value
	 * @return int
	 */
	protected function format($value)
	{
		if (is_null($value)) {
			return $value;
		}

		if (true === ($value instanceof Int)) {
			return $value->getValue();
		}

		return $value;
	}

	/**
	 * Boolean型に変換する
	 *
	 * @return Boolean
	 */
	public function toBoolean()
	{
		if (0 === $this->value) {
			return new Boolean(false);
		} elseif (1 === $this->value) {
			return new Boolean(true);
		}

		throw new IncorrectTypeException('Boolean型に変換できる値は0か1のみです');
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

	/**
	 * Float型に変換する
	 *
	 * @return Float
	 */
	public function toFloat()
	{
		return new Float((float)$this->value);
	}
}


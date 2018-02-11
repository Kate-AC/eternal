<?php

/**
 * Booleanクラス
 */

namespace System\Type\Primitive;

use System\Exception\IncorrectTypeException;

class Boolean extends AbstractPrimitive
{
	/**
	 * 値チェック
	 *
	 * @param mixed $value
	 * @throw IncorrectTypeException
	 */
	protected function check($value)
	{
		if (true === ($value instanceof Boolean)) {
			return;
		}

		if (is_null($value)) {
			return;
		}

		if (is_object($value) && false === ($value instanceof Boolean)) {
			throw new IncorrectTypeException('Boolean型ではないオブジェクトが渡された');
		}

		if (is_array($value)) {
			throw new IncorrectTypeException('配列が渡された');
		}

		if (!is_bool($value)) {
			throw new IncorrectTypeException('Booleanではない値が渡された');
		}
	}

	/**
	 * 値をフォーマットする
	 *
	 * @param mixced $value
	 * @return boolean
	 */
	protected function format($value)
	{
		if (is_null($value)) {
			return $value;
		}

		if (true === ($value instanceof Boolean)) {
			return $value->getValue();
		}

		return $value;
	}

	/**
	 * Int型に変換する
	 *
	 * @return Int
	 */
	public function toInt()
	{
		if (true === $this->value) {
			return new Int(1);
		} elseif (false === $this->value) {
			return new Int(0);
		}
	}

	/**
	 * String型に変換する
	 *
	 * @return String
	 */
	public function toString()
	{
		if (true === $this->value) {
			return new String('1');
		} elseif (false === $this->value) {
			return new String('0');
		}
	}

	/**
	 * Float型に変換する
	 *
	 * @return Float
	 */
	public function toFloat()
	{
		if (true === $this->value) {
			return new Float((float)1);
		} elseif (false === $this->value) {
			return new Float((float)0);
		}
	}
}


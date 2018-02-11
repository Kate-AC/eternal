<?php

/**
 * Stringクラス
 */

namespace System\Type\Primitive;

use System\Exception\IncorrectTypeException;

class String extends AbstractPrimitive
{
	/**
	 * 値チェック
	 *
	 * @param mixed $value
	 * @throw IncorrectTypeException
	 */
	protected function check($value)
	{
		if (true === ($value instanceof String)) {
			return;
		}

		if (is_null($value)) {
			return;
		}

		if (is_object($value) && false === ($value instanceof String)) {
			throw new IncorrectTypeException('String型ではないオブジェクトが渡された');
		}

		if (is_array($value)) {
			throw new IncorrectTypeException('配列が渡された');
		}

		if (!is_string($value)) {
			throw new IncorrectTypeException('Stringではない値が渡された');
		}
	}

	/**
	 * 値をフォーマットする
	 *
	 * @param mixced $value
	 * @return string
	 */
	protected function format($value)
	{
		if (is_null($value)) {
			return $value;
		}

		if (true === ($value instanceof String)) {
			return $value->getValue();
		}

		return $value;
	}

	/**
	 * nullかどうかチェックする
	 *
	 * @return Boolean
	 */
	public function isNull()
	{
		if ('' === $this->value || is_null($this->value)) {
			return new Boolean(true);
		}
		return new Boolean(false);
	}

	/**
	 * 置換する
	 *
	 * @parem String|string $from 元の文字列
	 * @parem String|string $to   置換後の文字列
	 * @return self
	 */
	public function replace($from = null, $to = null)
	{
		if (!is_string($from) && false === ($from instanceof String)) {
			throw new IncorrectTypeException('第1引数にString以外の値が与えられた');
		}
		if (!is_string($to) && false === ($to instanceof String)) {
			throw new IncorrectTypeException('第2引数にString以外の値が与えられた');
		}

		$from = is_string($from) ? $from : $from->getValue();
		$to   = is_string($to) ? $to : $to->getValue();

		$this->value = str_replace($from, $to, $this->value);

		return $this;
	}

	/**
	 * 文字列が存在するか調べる
	 *
	 * @parem String|string $value
	 * @return Boolean
	 */
	public function match($value = null)
	{
		if (!is_string($value) && false === ($value instanceof String)) {
			throw new IncorrectTypeException('第1引数にString以外の値が与えられた');
		}

		$value = is_string($value) ? $value : $value->getValue();

		if (false === strpos($this->value, $value)) {
			return new Boolean(false);
		}

		return new Boolean(true);
	}

	/**
	 * Boolean型に変換する
	 *
	 * @return Boolean
	 * @throw IncorrectTypeException
	 */
	public function toBoolean()
	{
		if ('1' === $this->value) {
			return new Boolean(true);
		} elseif ('0' === $this->value) {
			return new Boolean(false);
		}

		throw new IncorrectTypeException('Boolean型に変換可能な値は0か1の文字のみです');
	}

	/**
	 * Int型に変換する
	 *
	 * @return Int
	 * @throw IncorrectTypeException
	 */
	public function toInt()
	{
		if (1 === preg_match('/^\-?[0-9]+$/', $this->value)) {
			return new Int((int)$this->value);
		}

		throw new IncorrectTypeException('Int型に変換可能な値は-0～9の文字列のみです');
	}

	/**
	 * Float型に変換する
	 *
	 * @return Float
	 * @throw IncorrectTypeException
	 */
	public function toFloat()
	{
		if (1 === preg_match('/^-?([0-9]|[0-9]+\.[0-9]+)+$/', $this->value)) {
			return new Float((float)$this->value);
		}

		throw new IncorrectTypeException('Float型に変換可能な値は-.0～9の文字列のみです');
	}
}


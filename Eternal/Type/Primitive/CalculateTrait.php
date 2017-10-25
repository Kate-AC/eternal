<?php

/**
 * 計算用Trait
 */

namespace System\Type\Primitive;

trait CalculateTrait
{
	/**
	 * 加算する
	 *
	 * @param mixed $value
	 * @return this
	 */
	public function add($value = null)
	{
		$obj = new self($value);
		$this->value += $obj->getValue();

		return $this;
	}

	/**
	 * 乗算する
	 *
	 * @param mixed $value
	 * @return this
	 */
	public function times($value = null)
	{
		$obj = new self($value);
		$this->value *= $obj->getValue();

		return $this;
	}

	/**
	 * 除算する
	 *
	 * @param mixed $value
	 * @return this
	 */
	public function divided($value = null)
	{
		$obj = new self($value);
		if (true === ($this instanceof Int)) {
			$this->value = (int)floor($this->value / $obj->getValue());
		} elseif (true === ($this instanceof Float)) {
			$this->value = (float)($this->value / $obj->getValue());
		}
		return $this;
	}
}

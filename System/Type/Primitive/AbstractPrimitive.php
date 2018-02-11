<?php

/**
 * 型クラス
 */

namespace System\Type\Primitive;

use System\Exception\IncorrectTypeException;

abstract class AbstractPrimitive
{
	/**
	 * @var mixed
	 */
	protected $value;

	/**
	 * コンストラクタ
	 *
	 * @param mixed $value
	 */
	final public function __construct($value = null)
	{
		$this->setValue($value);
	}

	/**
	 * Getter
	 *
	 * @return int
	 */
	final public function getValue()
	{
		return $this->value;
	}

	/**
	 * Setter
	 *
	 * @param mixed $value
	 */
	final public function setValue($value)
	{
		$this->check($value);
		$this->value = $this->format($value);
	}

	/**
	 * 比較する
	 *
	 * @param string            $operator
	 * @param AbstractPrimitive $value
	 * @throw 
	 * @return boolean
	 */
	public function compare($operator, AbstractPrimitive $value)
	{
		if (get_class($this) !== get_class($value)) {
			throw new IncorrectTypeException(
				'自身のインスタンスと第2引数に渡されたインスタンスが異なります。' .
				'同じインスタンス同士でしか比較は行えません。'
			);
		}

		switch ($operator) {
			case '<>':
			case '!==':
				$result = $this->value !== $value->getValue();
				break;
			case '===':
				$result = $this->value === $value->getValue();
				break;
			case '<=':
				$result = $this->value <= $value->getValue();
				break;
			case '>=':
				$result = $this->value >= $value->getValue();
				break;
			case '<':
				$result = $this->value < $value->getValue();
				break;
			case '>':
				$result = $this->value > $value->getValue();
				break;
			default:
				throw new IncorrectTypeException(
					'第1引数に指定できる値は<>,!==,===,<=,>=,<,>のみです。' . 
					'他の比較演算子を使用する場合は、プリミティブ型のクラス内で' .
					'compareメソッドをオーバーライドして下さい。'
				);
		}

		return new Boolean($result);
	}

	/**
	 * 値チェック
	 *
	 * @param mixed $value
	 * @throw DiffelentPrimitiveException
	 */
	abstract protected function check($value);

	/**
	 * 値をフォーマットする
	 *
	 * @param mixed $value
	 * @return mixed
	 */
	abstract protected function format($value);

	/**
	 * nullかどうかチェックする
	 *
	 * @return boolean
	 */
	public function isNull()
	{
		return new Boolean(is_null($this->value) ? true : false);
	}
}


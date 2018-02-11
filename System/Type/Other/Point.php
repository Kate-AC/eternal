<?php

/**
 * 緯度経度を保存するクラス
 */

namespace System\Type\Other;

use System\Exception\IncorrectTypeException;

class Point
{
	/**
	 * @var mixed
	 */
	private $lat;

	/**
	 * @var mixed
	 */
	private $lng;
	
	/**
	 * コンストラクタ
	 *
	 * @param mixed $a 'POINT(lat lng)'またはlat
	 * @param mixed $b lng
	 */
	public function __construct($a, $b = null)
	{
		if (is_null($b)) {
			if (1 !== preg_match('/POINT\((.*)\)/', $a, $match)) {
				throw new IncorrectTypeException(implode("\r\n", [
					sprintf('POINT文字列の形式が正しくありません(%s) ', $a),
					'次の形式になるようにしてください > POINT(lat lng)'
				]));
			}
			$match[1]  = str_replace(')"', '', $match[1]);
			$latLng    = explode(' ', $match[1]);
			$this->lat = $latLng[1];
			$this->lng = $latLng[0];
		} else {
			$this->lat = $a;
			$this->lng = $b;
		}
	}

	/**
	 * 緯度を取得する
	 *
	 * @return mixed
	 */
	public function getLat()
	{
		return $this->lat;
	}

	/**
	 * 経度を取得する
	 *
	 * @return mixed
	 */
	public function getLng()
	{
		return $this->lng;
	}
}

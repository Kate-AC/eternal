<?php

/**
 * クエリをフェッチするトレイト
 */

namespace System\Database\MySql\Query;

use System\Exception\DatabaseException;

trait QueryFetchTrait
{
	/**
	 * データを1件取得する
	 *
	 * @param boolean $isArray
	 * @return object
	 */
	public function fetch($isArray = false)
	{
		$query    = $this->create();
		$prepared = $this->escape($query);
		$result   = $prepared->fetch();

		if (false === $result) {
			return null;
		}

		$united = false === $isArray ? $this->unite([$result]) : $this->uniteArray([$result]);

		return array_shift($united);
	}

	/**
	 * データを全て取得する
	 * 
	 * @param boolean $isArray
	 * @return object[]
	 */
	public function fetchAll($isArray = false)
	{
		$query      = $this->create();
		$prepared   = $this->escape($query);
		$resultList = $prepared->fetchAll();

		return false === $isArray ? $this->unite($resultList) : $this->uniteArray($resultList);
	}

	/**
	 * 指定したカラムの値を添え字にしたデータを全て取得する
	 *
	 * @param string  $key 指定したキー
	 * @param boolean $isArray
	 * @param object[]
	 */
	public function fetchAllByKey($key = null, $isArray = false)
	{
		$query      = $this->create();
		$prepared   = $this->escape($query);
		$resultList = $prepared->fetchAll();

		if (is_null($key)) {
			if (isset($this->primaryKeys[0])) {
				//キーの指定がない場合は一番最初のプライマリーキーを取得
				$key = $this->primaryKeys[0];
			} else {
				if (false === $isArray) {
					throw new DatabaseException('fetchAllByKeyを選択しましたが、プライマリーキーが存在しません');
				} else {
					throw new DatabaseException('fetchAllAsArrayByKeyを選択しましたが、プライマリーキーが存在しません');
				}
			}
		}

		return false === $isArray ? $this->unite($resultList, $key) : $this->uniteArray($resultList, $key);
	}

	/**
	 * データを1件配列で取得する
	 *
	 * @return string[]
	 */
	public function fetchAsArray()
	{
		return $this->fetch(true);
	}

	/**
	 * データを全て配列で取得する
	 *
	 * @return string[]
	 */
	public function fetchAllAsArray()
	{
		return $this->fetchAll(true);
	}

	/**
	 * 指定したカラムの値を添え字にしたデータを全て配列で取得する
	 *
	 * @param string $key 指定したキー
	 * @return object[]
	 */
	public function fetchAllAsArrayByKey($key = null)
	{
		return $this->fetchAllByKey($key, true);
	}

	/**
	 * データ数を取得する
	 *
	 * @return int
	 */
	public function count()
	{
		$query    = $this->create();
		$prepared = $this->escape($query);
		return (int)$prepared->rowCount();
	}

	/**
	 * クエリをエスケープする
	 *
	 * @param $query
	 * @return $hoge
	 */
	protected function escape($query)
	{
		$prepare = $this->connection->getAuto()->prepare($query);
		$prepare->setFetchMode(\PDO::FETCH_NAMED);
		$prepare->execute($this->placeholder);
		return $prepare;
	}
}


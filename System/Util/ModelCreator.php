<?php

/**
 * モデルのエンティティとスケルトンを生成するクラス
 */

namespace System\Util;

use System\Database\MySql\Connection;
use System\Exception\SystemException;
use System\Util\StringOperator;

class ModelCreator
{
	/**
	 * @var Connection
	 */
	private $connection;

	/**
	 * モデル名
	 *
	 * @var string
	 */
	private $modelName;

	/**
	 * テーブル名
	 *
	 * @var string
	 */
	private $tableName;

	/**
	 * 名前空間の配列
	 *
	 * @var string[]
	 */
	private $explodedNamespaceList;

	/**
	 * カラム情報の配列
	 *
	 * @var string[]
	 */
	private $columnInfoList = [];

	/**
	 * プライマリーキーの配列
	 *
	 * @var string[]
	 */
	private $primaryKeyList = [];

	/**
	 * POINT型を使用するかどうか
	 *
	 * @var boolean
	 */
	private $usePoint = false;

	/**
	 * コンストラクタ
	 *
	 * @param Connection
	 */
	public function __construct(Connection $connection)
	{
		$this->connection = $connection;
	}

	/**
	 * 名前空間から必要な情報をセットする
	 *
	 * @param string $namespace
	 */
	public function initialize($namespace)
	{
		$explodedNamespaceList = explode('\\', $namespace);
		$this->explodedNamespaceList = $explodedNamespaceList;
		$this->modelName = end($explodedNamespaceList);
		$this->tableName = $this->getTableName();
	}

	/**
	 * モデル名を取得する
	 *
	 * @return string
	 */
	public function getModelName()
	{
		return $this->modelName;
	}

	/**
	 * 名前空間からテーブル名を取得する
	 *
	 * @return string
	 * @throws SystemException
	 */
	private function getTableName()
	{
		$pdo     = $this->connection->getAuto();
		$prepare = $pdo->query('SHOW TABLES');
		$prepare->setFetchMode(\PDO::FETCH_ASSOC);
		$resultList = $prepare->fetchAll();

		foreach ($resultList as $result) {
			$tableName = array_shift($result);
			if ($this->modelName === StringOperator::tableToClassName($tableName)) {
				return $tableName;
			}
		}

		throw new SystemException('名前空間に対応するテーブルが存在しない');
	}

	/**
	 * ディレクトリを生成する
	 */
	public function makeDirectory()
	{
		$path = MODEL_DIR;
		$end  = count($this->explodedNamespaceList) - 1;
		foreach ($this->explodedNamespaceList as $key => $string) {
			$pattern = sprintf('/\/%s\//', $string);

			if (1 === preg_match($pattern, MODEL_DIR) || $key === $end) {
				continue;
			}

			$path .= ucfirst($string) . '/';
			if (false === is_dir($path)) {
				mkdir($path);
			}
		}
	}

	/**
	 * カラムの情報を取得してセットする
	 *
	 * @return string[]
	 */
	private function parseColumn()
	{
		$pdo     = $this->connection->getAuto();
		$prepare = $pdo->query(sprintf('SHOW COLUMNS FROM %s', $this->tableName));
		$prepare->setFetchMode(\PDO::FETCH_ASSOC);
		$resultList = $prepare->fetchAll();

		foreach ($resultList as $result) {
			if ('PRI' === $result['Key']) {
				$primaryKeyList[] = sprintf("'%s'", $result['Field']);
			}

			$default = !is_null($result['Default']) ? $result['Default'] : 'null';
			if ('CURRENT_TIMESTAMP' === $default) {
				$default = 'new \DateTime()';
			}

			$this->columnInfoList[] = [
				'column'  => $result['Field'],
				'type'    => $this->getType($result['Type']),
				'cast'    => $this->getCastType($result['Type']),
				'getter'  => StringOperator::columnToGetter($result['Field']),
				'default' => $default
			];
		}
	}

	/**
	 * カラムのタイプから型を取得
	 *
	 * @return string
	 */
	private function getType($type)
	{
		if (false !== stripos($type, 'int')) {
			return 'int';
		}

		if (false !== stripos($type, 'time') || false !== stripos($type, 'date')) {
			return '\DateTime';
		}

		if (false !== stripos($type, 'geometry')) {
			$this->usePoint = true;
			return 'Point';
		}

		return 'string';
	}

	/**
	 * カラムのタイプからキャスト方法を取得
	 *
	 * @return string
	 */
	private function getCastType($type)
	{
		if (false !== stripos($type, 'int')) {
			return 'intval';
		}

		if (false !== stripos($type, 'time') || false !== stripos($type, 'date')) {
			return 'new \DateTime';
		}

		if (false !== stripos($type, 'geometry')) {
			return 'new Point';
		}

		return 'strval';
	}

	/**
	 * プロパティを生成する
	 *
	 * @return string
	 */
	private function getProperty()
	{
		$property = '';
		foreach ($this->columnInfoList as $columnInfo) {
			$property .= <<<EOD
	/**
	 * @model {$columnInfo['type']}
	 */
	protected \${$columnInfo['column']};


EOD;
		}
		return $property;
	}

	/**
	 * Setterを生成する
	 *
	 * @return string
	 */
	private function getSetter()
	{
		$setter = '';
		foreach ($this->columnInfoList as $columnInfo) {
			$setter .= <<<EOD
		if (isset(\$properties['{$columnInfo['column']}'])) {

EOD;
			if ('\DateTime' === $columnInfo['type']) {
				$setter .= <<<EOD
			if (!(\$properties['{$columnInfo['column']}'] instanceof \DateTime)) {
				\$properties['{$columnInfo['column']}'] = {$columnInfo['cast']}(\$properties['{$columnInfo['column']}']);
			}

EOD;
			} else {
				$setter .= <<<EOD
			\$properties['{$columnInfo['column']}'] = {$columnInfo['cast']}(\$properties['{$columnInfo['column']}']);

EOD;
			}
			$setter .= <<<EOD
		} else {

EOD;

			if ('\DateTime' === $columnInfo['type'] && 'null' !== $columnInfo['default']) {
				$columnInfo['default'] = sprintf("'%s'", $columnInfo['default']);
			}

			if ("" === $columnInfo['default']) {
				$columnInfo['default'] = 'null';
			}

			$setter .= <<<EOD
			\$properties['{$columnInfo['column']}'] = {$columnInfo['default']};
		}


EOD;
		}
		return $setter;
	}

	/**
	 * Getterを生成する
	 *
	 * @return string
	 */
	private function getGetter()
	{
		$getter = '';
		foreach ($this->columnInfoList as $columnInfo) {
			$getter .= <<<EOD

	/**
	 * @return {$columnInfo['type']}
	 */
	public function {$columnInfo['getter']}()
	{
		return \$this->{$columnInfo['column']};
	}
EOD;
		}
		return $getter;
	}

	/**
	 * エンティティが存在するかどうか調べる
	 *
	 * @return boolean
	 */
	public function existEntity()
	{
		return file_exists(sprintf('%s%s.php', PUBLIC_DIR, implode('/', $this->explodedNamespaceList)));
	}

	/**
	 * エンティティを生成する
	 *
	 * @return boolean
	 */
	public function createEntity()
	{
		$explodedNamespaceList = $this->explodedNamespaceList;
		$last = count($explodedNamespaceList) - 1;
		unset($explodedNamespaceList[$last]);
		$namespace = implode('\\', $explodedNamespaceList);
		$entity = <<<EOD
<?php

/**
 * {$this->modelName}モデルのエンティティ
 */

namespace {$namespace};

class {$this->modelName} extends {$this->modelName}Skeleton
{
}
EOD;
		$path = sprintf('%s%s.php', PUBLIC_DIR, implode('/', $this->explodedNamespaceList));
		return false !== file_put_contents($path, $entity) ? true : false;
	}

	/**
	 * スケルトンを生成する
	 *
	 * @return boolean
	 */
	public function createSkeleton()
	{
		$this->parseColumn();
		$explodedNamespaceList = $this->explodedNamespaceList;
		$last = count($explodedNamespaceList) - 1;
		unset($explodedNamespaceList[$last]);
		$namespace      = implode('\\', $explodedNamespaceList);
		$primaryKeyList = sprintf('[%s]', implode(', ', $this->primaryKeyList));

		$point = '';
		if (true === $this->usePoint) {
			$point = 'use System\\Type\\Other\\Point;' . "\n";
		}

		$skeleton = <<<EOD
<?php

/**
 * {$this->modelName}モデルのスケルトン
 */

namespace {$namespace};

use System\Database\MySql\BaseModel;
{$point}
class {$this->modelName}Skeleton extends BaseModel
{
{$this->getProperty()}	/**
	 * @param mixed[] \$properties
	 * @return {$this->modelName}
	 */
	public static function make(array \$properties)
	{
{$this->getSetter()}		\$instance = new static();
		return \$instance(\$properties);
	}

	/**
	 * @return string
	 */
	public static function getTableName()
	{
		return '{$this->tableName}';
	}

	/**
	 * @return string[]
	 */
	public static function getPrimaryKeys()
	{
		return {$primaryKeyList};
	}	{$this->getGetter()}
}
EOD;
		$path = sprintf('%s%sSkeleton.php', SRC_DIR, implode('/', $this->explodedNamespaceList));
		return false !== file_put_contents($path, $skeleton) ? true : false;
	}
}
